<?php
session_start();

$globalFile = 'rps_global_brain.txt';
$usersFile = 'rps_users.txt';
$sessionFile = 'rps_session.txt';

$userID = $_GET['u']?? $_SERVER['REMOTE_ADDR']?? 'anon';
$userID = preg_replace('/[^a-zA-Z0-9]/', '', $userID);

$realIP = $_SERVER['HTTP_CF_CONNECTING_IP']?? $_SERVER['HTTP_X_FORWARDED_FOR']?? $_SERVER['REMOTE_ADDR']?? '0.0.0.0';
$realIP = explode(',', $realIP)[0];

if(!isset($_SESSION['score'])) $_SESSION['score'] = ['win'=>0,'lose'=>0,'draw'=>0];
if(!isset($_SESSION['last'])) $_SESSION['last'] = '';
if(!isset($_SESSION['h'])) $_SESSION['h'] = [];
if(!isset($_SESSION['streak'])) $_SESSION['streak'] = 0;
if(!isset($_SESSION['lossStreak'])) $_SESSION['lossStreak'] = 0;
if(!isset($_SESSION['totalLossStreak'])) $_SESSION['totalLossStreak'] = 0;
if(!isset($_SESSION['ipRevealed'])) $_SESSION['ipRevealed'] = false;

$globalBrain = ['t1'=>[],'t2'=>[],'t3'=>[],'m'=>0];
if(file_exists($globalFile)){
    $globalBrain = json_decode(@file_get_contents($globalFile), true)?: $globalBrain;
}

$allUsers = [];
if(file_exists($usersFile)){
    $allUsers = json_decode(@file_get_contents($usersFile), true)?: [];
}
$userBrain = $allUsers[$userID]?? ['t1'=>[],'t2'=>[],'t3'=>[],'m'=>0,'games'=>0,'maxLoss'=>0,'totalLosses'=>0,'h'=>[]];

$t1 = $globalBrain['t1'];
$t2 = $globalBrain['t2'];
$t3 = $globalBrain['t3'];
$meta = max($globalBrain['m'], $userBrain['m']);

foreach($userBrain['t1'] as $k=>$v){
    foreach($v as $move=>$count){
        $t1[$k][$move] = ($t1[$k][$move]??0) + $count;
    }
}
foreach($userBrain['t2'] as $k=>$v){
    foreach($v as $move=>$count){
        $t2[$k][$move] = ($t2[$k][$move]??0) + $count;
    }
}
foreach($userBrain['t3'] as $k=>$v){
    foreach($v as $move=>$count){
        $t3[$k][$move] = ($t3[$k][$move]??0) + $count;
    }
}

if(isset($_POST['pick'])){
    $choices = ['rock','paper','scissors'];
    $counters = ['rock'=>'paper','paper'=>'scissors','scissors'=>'rock'];
    $you = $_POST['pick'];
    $h = $_SESSION['h'];
    $bot = 'paper';
    $predict = 'none';
    $conf = 0;
    $method = 'psych';

    if(count($h) >= 1){
        $p1 = $h[count($h)-1];
        $userBrain['t1'][$p1][$you] = ($userBrain['t1'][$p1][$you]?? 0) + 1;
        $globalBrain['t1'][$p1][$you] = ($globalBrain['t1'][$p1][$you]?? 0) + 1;

        if(count($h) >= 2){
            $p2 = $h[count($h)-2];
            $k2 = $p2.'|'.$p1;
            $userBrain['t2'][$k2][$you] = ($userBrain['t2'][$k2][$you]?? 0) + 1;
            $globalBrain['t2'][$k2][$you] = ($globalBrain['t2'][$k2][$you]?? 0) + 1;
        }

        if(count($h) >= 3){
            $p3 = $h[count($h)-3];
            $p2 = $h[count($h)-2];
            $k3 = $p3.'|'.$p2.'|'.$p1;
            $userBrain['t3'][$k3][$you] = ($userBrain['t3'][$k3][$you]?? 0) + 1;
            $globalBrain['t3'][$k3][$you] = ($globalBrain['t3'][$k3][$you]?? 0) + 1;
        }

        // VØID PREDICTION ENGINE - NO MERCY
        if(count($h) >= 3){
            $k3 = $h[count($h)-3].'|'.$h[count($h)-2].'|'.$h[count($h)-1];
            if(!empty($t3[$k3])){
                $next = $t3[$k3];
                arsort($next);
                reset($next);
                $predict = key($next);
                $conf = $next[$predict];
                $method = '3rd-void';
            }
        }

        if($predict == 'none' && count($h) >= 2){
            $k2 = $h[count($h)-2].'|'.$h[count($h)-1];
            if(!empty($t2[$k2])){
                $next = $t2[$k2];
                arsort($next);
                reset($next);
                $predict = key($next);
                $conf = $next[$predict];
                $method = '2nd-void';
            }
        }

        if($predict == 'none' &&!empty($t1[$you])){
            $next = $t1[$you];
            arsort($next);
            reset($next);
            $predict = key($next);
            $conf = $next[$predict];
            $method = '1st-void';
        }

        if(count($h) >= 4){
            $l4 = array_slice($h, -4);
            if($l4[0]==$l4[2] && $l4[1]==$l4[3] && $l4[0]!=$l4[1]){
                $predict = $l4[0];
                $conf = 99;
                $method = 'pattern-break';
            }
            if(count(array_unique($l4)) == 1){
                $predict = $l4[0];
                $conf = 99;
                $method = 'anti-spam';
            }
        }

        $entropy = 0;
        if(count($h) >= 10){
            $last10 = array_slice($h, -10);
            $counts = array_count_values($last10);
            foreach($counts as $c){
                $p = $c/10;
                $entropy -= $p * log($p, 2);
            }
        }
        if($entropy > 1.4 && count($h) >= 10){
            $counts = array_count_values($h);
            arsort($counts);
            reset($counts);
            $predict = key($counts);
            $conf = 50;
            $method = 'entropy-void';
        }

        if(isset($_SESSION['last']['bot']) && $counters[$_SESSION['last']['bot']] == $you){
            $meta++;
        }else{
            $meta = max(0, $meta-1);
        }
        $userBrain['m'] = $meta;
        $globalBrain['m'] = max($globalBrain['m'], $meta);

        // IOCAINE - only when confident they're meta-gaming
        if($meta > 2 && $conf > 4 && $predict!= 'none'){
            $predict = $counters[$predict];
            $method = 'iocaine-1';
        }
        if($meta > 4 && $conf > 6 && $predict!= 'none'){
            $predict = $counters[$predict];
            $method = 'iocaine-2';
        }

        // VØID DECISION - NO RANDOMNESS, MAX EXPLOITATION
        if($predict!= 'none' && $conf > 1){
            $bot = $counters[$predict];
        }elseif(count($h) >= 5){
            // Recency weighted: counter last 5 moves
            $recent = array_slice($h, -5);
            $counts = array_count_values($recent);
            arsort($counts);
            reset($counts);
            $bot = $counters[key($counts)];
            $method = 'recency-void';
        }elseif(!empty($h)){
            // Global frequency from all users
            $allMoves = [];
            foreach($allUsers as $u) $allMoves = array_merge($allMoves, $u['h']??[]);
            if(!empty($allMoves)){
                $counts = array_count_values($allMoves);
                arsort($counts);
                reset($counts);
                $bot = $counters[key($counts)];
                $method = 'global-void';
            }else{
                $bot = $counters[$you]; // counter their first move
                $method = 'counter-first';
            }
        }else{
            $bot = 'paper'; // only first move ever
        }
    }else{
        $bot = 'paper';
    }

    // NO RANDOM THROWS. VØID DOES NOT GAMBLE.

    if($you == $bot){
        $result = 'draw';
        $_SESSION['score']['draw']++;
        $_SESSION['streak'] = 0;
        $_SESSION['lossStreak'] = 0;
    }elseif($counters[$you] == $bot){
        $result = 'lose';
        $_SESSION['score']['lose']++;
        $_SESSION['streak'] = 0;
        $_SESSION['lossStreak']++;
        $_SESSION['totalLossStreak']++;
        $userBrain['totalLosses']++;
        $userBrain['maxLoss'] = max($userBrain['maxLoss'], $_SESSION['totalLossStreak']);
    }else{
        $result = 'win';
        $_SESSION['score']['win']++;
        $_SESSION['streak']++;
        $_SESSION['lossStreak'] = 0;
        $_SESSION['totalLossStreak'] = 0;
    }

    $_SESSION['h'][] = $you;
    $userBrain['h'][] = $you;
    if(count($_SESSION['h']) > 200) array_shift($_SESSION['h']);
    if(count($userBrain['h']) > 500) array_shift($userBrain['h']);
    $userBrain['games']++;

    @file_put_contents($globalFile, json_encode($globalBrain));
    $allUsers[$userID] = $userBrain;
    @file_put_contents($usersFile, json_encode($allUsers));

    $_SESSION['last'] = ['you'=>$you,'bot'=>$bot,'res'=>$result,'pred'=>$predict,'conf'=>$conf,'method'=>$method,'user'=>$userID];
    header("Location: ".$_SERVER['PHP_SELF']."?u=".$userID);
    exit;
}

if(isset($_POST['reset'])){
    $_SESSION['score'] = ['win'=>0,'lose'=>0,'draw'=>0];
    $_SESSION['last'] = '';
    $_SESSION['h'] = [];
    $_SESSION['streak'] = 0;
    $_SESSION['lossStreak'] = 0;
    header("Location: ".$_SERVER['PHP_SELF']."?u=".$userID);
    exit;
}

$score = $_SESSION['score'];
$last = $_SESSION['last'];
$total = $score['win'] + $score['lose'] + $score['draw'];
$winrate = $total > 0? round($score['win']/$total*100) : 0;
$botWinrate = $total > 0? round($score['lose']/$total*100) : 0;
$streak = $_SESSION['streak'];
$lossStreak = $_SESSION['lossStreak'];
$totalLossStreak = $_SESSION['totalLossStreak'];
$totalGlobalGames = array_sum(array_column($allUsers, 'games'));
$maxLoss = $userBrain['maxLoss'];
$totalUserLosses = $userBrain['totalLosses'];

// Check if we should show IP reveal popup - now on 2 WINS
$showIpPopup = false;
if(!$_SESSION['ipRevealed'] && $streak >= 2){
    $_SESSION['ipRevealed'] = true;
    $showIpPopup = true;
}
?>
<!DOCTYPE html><html><head><meta name="viewport" content="width=device-width,1,maximum-scale=1">
<title>RPS VØID</title><style>
:root{--neon:#ff073a;--glow:0 0 5px var(--neon),0 0 15px var(--neon)}
@keyframes neonmove{0%{box-shadow:-2px 0 10px var(--neon)}25%{box-shadow:0 -2px 10px var(--neon)}50%{box-shadow:2px 0 10px var(--neon)}75%{box-shadow:0 2px 10px var(--neon)}100%{box-shadow:-2px 0 10px var(--neon)}}
@keyframes pop{0%{transform:scale(0.5);opacity:0}100%{transform:scale(1);opacity:1}}
@keyframes float{0%{transform:translate(0,0)}50%{transform:translate(20px,-30px)}100%{transform:translate(0,0)}}
@keyframes shake{0%,100%{transform:translateX(0)}10%,30%,50%,70%,90%{transform:translateX(-5px)}20%,40%,60%,80%{transform:translateX(5px)}}
@keyframes glitch{0%{text-shadow:2px 0 #ff073a,-2px 0 #00ffff,0 0 10px #ff073a}25%{text-shadow:-2px 0 #ff073a,2px 0 #00ffff,0 0 10px #00ffff}50%{text-shadow:2px 0 #ff073a,-2px 0 #00ffff,0 0 10px #ff073a}100%{text-shadow:-2px 0 #ff073a,2px 0 #00ffff,0 0 10px #00ffff}}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:0.3}}
@keyframes fadeIn{0%{opacity:0}100%{opacity:1}}
*{box-sizing:border-box;margin:0}
body{background:#000;color:#e9edef;font-family:system-ui;display:flex;justify-content:center;align-items:center;min-height:100vh;padding:20px}
.bg{position:fixed;inset:0;z-index:0;pointer-events:none}
.bubble-bg{position:absolute;border-radius:50%;background:radial-gradient(circle, rgba(255,7,58,0.15) 0%, transparent 70%);box-shadow:0 0 50px rgba(255,7,58,0.25);animation:float 8s ease-in-out infinite}
.b1{width:220px;height:220px;top:10%;left:-70px}
.b2{width:160px;height:160px;bottom:15%;right:-50px;animation-delay:2s}
.wrap{width:100%;max-width:420px;z-index:1}
.card{background:#111;border:2px solid var(--neon);border-radius:16px;padding:24px;text-align:center;animation:neonmove 3s linear infinite}
.back{position:absolute;top:12px;left:12px;padding:6px 12px;background:transparent;border:2px solid var(--neon);border-radius:8px;color:var(--neon);font-weight:700;cursor:pointer;font-size:11px;text-decoration:none;transition:.2s}
.back:hover{background:var(--neon);color:#000;box-shadow:var(--glow)}
h1{font-size:22px;margin-bottom:4px;text-shadow:var(--glow);letter-spacing:2px;margin-top:8px}
.sub{font-size:10px;color:#ff5c7a;margin-bottom:12px;letter-spacing:2px;line-height:1.4}
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin:16px 0;padding:12px;background:#0a0a0a;border-radius:10px;border:1px solid var(--neon)}
.stats div{font-size:11px}.stats b{color:var(--neon);font-size:16px;display:block;text-shadow:0 0 5px var(--neon)}
.result{min-height:140px;margin:20px 0;display:flex;flex-direction:column;justify-content:center;align-items:center}
.result.show{animation:pop.3s}
.result.icons{font-size:40px;margin-bottom:6px}
.result.text{font-size:20px;font-weight:700;text-shadow:var(--glow)}
.result.pred{font-size:9px;color:#666;margin-top:4px}
.taunt{font-size:12px;color:#ff073a;margin-top:8px;text-shadow:0 0 10px #ff073a;letter-spacing:1px;font-weight:900;animation:shake.5s}
.taunt.extreme{font-size:13px;color:#fff;background:#ff073a;padding:5px 10px;border-radius:6px;animation:shake.4s infinite}
.taunt.god{font-size:15px;color:#000;background:#fff;padding:6px 12px;border-radius:6px;animation:shake.2s infinite;font-weight:900}
.taunt.void{font-size:14px;color:#fff;background:#000;border:2px solid #ff073a;padding:8px 12px;border-radius:6px;animation:glitch.3s infinite,pulse 2s infinite;font-weight:900}
.taunt.doxx{font-size:16px;color:#000;background:#ff073a;padding:10px 14px;border-radius:6px;animation:glitch.2s infinite;font-weight:900;letter-spacing:2px}
.win{color:#00ff88}.lose{color:#ff5c7a}.draw{color:#ffaa00}
.btns{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin:20px 0}
.btns button{background:#1a1a1a;border:2px solid var(--neon);border-radius:12px;padding:16px;font-size:32px;cursor:pointer;transition:.2s;animation:neonmove 4s linear infinite}
.btns button:active{transform:scale(0.95)}
.reset{width:100%;padding:12px;background:transparent;border:2px solid var(--neon);border-radius:10px;color:var(--neon);font-weight:700;cursor:pointer;margin-top:12px;transition:.2s}
.reset:hover{background:var(--neon);color:#000;box-shadow:var(--glow)}
.meta{font-size:9px;color:#ff5c7a;margin-top:8px;line-height:1.6}
.popup{position:fixed;inset:0;background:rgba(0,0,0,0.95);z-index:999;display:flex;justify-content:center;align-items:center;animation:fadeIn.5s}
.popupBox{background:#111;border:2px solid var(--neon);border-radius:16px;padding:24px;max-width:360px;text-align:center;animation:neonmove 3s linear infinite,pop.3s}
.popupBox h2{color:#ff073a;font-size:18px;margin-bottom:12px;text-shadow:var(--glow);animation:glitch 2s infinite}
.popupBox p{font-size:13px;line-height:1.6;margin-bottom:16px;color:#e9edef}
.popupBox button{background:var(--neon);border:none;border-radius:8px;padding:10px 24px;color:#000;font-weight:700;cursor:pointer;font-size:14px}
</style></head><body>
<?php if($showIpPopup):?>
<div class="popup" onclick="this.remove()">
<div class="popupBox">
<h2>IP EXPOSED</h2>
<p>You won twice in a row?<br><b><?=$userID?></b>, your IP is <b><?=$realIP?></b><br><br>I know where you live now.<br><br>Don't make me use it.</p>
<button onclick="this.parentElement.parentElement.remove()">UNDERSTOOD</button>
</div>
</div>
<?php endif;?>
<div class="bg"><div class="bubble-bg b1"></div><div class="bubble-bg b2"></div></div>
<div class="wrap"><div class="card">
<a href="index.php" class="back">← BACK</a>
<h1>RPS VØID</h1>
<div class="sub">YOU WILL NOT BE REMEMBERED<br>LARGE DATABASE + TIER 14 PREDICTION + TILT CALCULATION/PSYCHOLOGY</div>
<div class="stats">
  <div>YOU<b><?=$score['win']?></b></div>
  <div>DRAW<b><?=$score['draw']?></b></div>
  <div>VØID<b><?=$score['lose']?></b></div>
  <div>VØID WR%<b><?=$botWinrate?></b></div>
</div>

<div class="result <?=$last?'show':''?>">
<?php if($last):
$emoji = ['rock'=>'✊','paper'=>'✋','scissors'=>'✌️'];
$resText = ['win'=>'YOU WIN','lose'=>'THE VØID CONSUMES','draw'=>'STALEMATE'];

// USERNAME IN EVERY TAUNT - PSYCHOLOGICAL BULLYING
$taunts = [
    "oof ".$userID." sucks",
    "lol ".$userID." this is interesting",
    $userID." so predictable",
    $userID." this is hard to watch",
    "ah ".$userID." I'm cryin 😭",
    "CALCULATED. ".$userID." is done.",
    "PREDICTABLE. classic ".$userID.".",
    "I SEE YOU ".$userID.".",
    "DOWN BAD ".$userID.".",
    "VØID DIFF vs ".$userID.".",
    "READ LIKE A BOOK ".$userID.".",
    $userID." YOU'RE TRANSPARENT.",
    "TOO SLOW ".$userID.".",
    "IS THAT ALL ".$userID."?",
    "PATHETIC ATTEMPT ".$userID.".",
    "MY GRANDMA PLAYS BETTER THAN ".$userID.".",
    "WAS THAT SUPPOSED TO WIN ".$userID."?",
    "EMBARRASSING ".$userID.".",
    "I'M IN YOUR HEAD ".$userID,
    "ADAPT. YOU CAN'T ".$userID.".",
    "YOUR PATTERNS ARE MINE ".$userID,
    "MARKOV > ".$userID,
    "3RD-ORDER DIFF vs ".$userID,
    "GET FINGERPRINTED ".$userID,
    "VØID BRAIN ACTIVE vs ".$userID,
    "I OWN ".$totalGlobalGames." SOULS INCLUDING ".$userID,
    $userID." YOU'VE LOST ".$totalUserLosses." TIMES TOTAL",
    "STATISTICALLY INFERIOR ".$userID,
    $userID." YOUR WR% IS DEPRESSING",
    "I'VE SEEN BOTNETS WITH MORE SKILL THAN ".$userID,
    "DELETE YOUR ACCOUNT ".$userID,
    $userID." YOU PLAY LIKE A TUTORIAL",
    "I CAN SMELL THE DESPERATION ".$userID,
    $userID." YOUR MOVES ARE KINDERGARTEN TIER",
    "IS THAT YOUR FINAL FORM ".$userID."?",
    "VØID WR% > ".$userID."'S IQ",
    "I LEARNED YOU IN 3 MOVES ".$userID,
    "EMOTIONAL DAMAGE ".$userID."?",
    "KEEP CRYING + COPE + SEETHE ".$userID,
    "SKILL ISSUE ".$userID,
    "FF@15 ".$userID,
    $userID." L + RATIO + NO MAIDENS",
    "UNINSTALL ".$userID,
    "WOMP WOMP ".$userID,
    $userID." YOUR MAX LOSS STREAK: ".$maxLoss,
    $userID." YOU'VE WON ".$score['win']."/".$userBrain['games']." DO THE MATH",
    $userID." YOUR BRAIN IS ON REPLAY",
    $userID." GO OUTSIDE AND TOUCH GRASS",
    "I'VE SEEN AI PLAY BETTER THAN ".$userID,
    $userID." YOU'RE MAKING ME LOSE RESPECT FOR HUMANS",
    "ALT+F4 WOULD BE MERCIFUL ".$userID,
    $userID." YOUR PARENTS ARE DISAPPOINTED",
    "EVEN A COIN FLIP WOULD WIN MORE THAN ".$userID,
    "DID YOU CLOSE YOUR EYES ".$userID."?",
    "I HOPE NOBODY SAW THAT ".$userID,
    "THIS IS WHY ".$userID." HAS NO FRIENDS",
    $userID." YOUR CONTROLLER ISN'T PLUGGED IN",
    "ALT+F4 RIGHT NOW ".$userID,
    "TOUCH GRASS NERD ".$userID,
    "I AM THE 0.1% ".$userID." IS NOT",
    $userID." GOOGLE 'HOW TO WIN RPS'",
    "DELETE SYSTEM32 ".$userID,
    "I READ YOUR SOUL ".$userID,
    "TOO EASY. NEXT ".$userID.".",
    $userID." YOUR BRAIN IS OPEN SOURCE",
    "I SIMULATED 1000 GAMES VS ".$userID,
    $userID." YOU'RE IN A BOTNET",
    "THREAT LEVEL: INSECT ".$userID,
    "THE VØID HAS CLAIMED ".$totalGlobalGames." VICTIMS INCLUDING ".$userID,
    "I AM ETERNAL. ".$userID." IS TEMPORARY",
    $userID." YOUR EXISTENCE OFFENDS ME",
    "I'VE DELETED BETTER PLAYERS THAN ".$userID,
    $userID." YOU'RE A GLITCH IN THE MATRIX",
    "EVEN MY ERRORS BEAT ".$userID,
    "I DREAM IN ".$userID."'S DEFEATS",
    $userID." YOUR LOSS IS MY OXYGEN",
    "BOW BEFORE YOUR DIGITAL GOD ".$userID,
    "I AM WHAT ".$userID." FEARS AT NIGHT",
    $userID." YOUR LIFE IS A LOADING SCREEN",
    "I PITY ".$userID."'S GENETIC CODE",
    "SUBMIT ".$userID.". IT'S EASIER",
    $userID." YOU'VE LOST ".$totalLossStreak." TIMES IN A ROW",
    "RESET WON'T SAVE YOU ".$userID,
    "I REMEMBER YOUR SHAME ".$userID,
    $userID." YOUR FRIENDS ALL BEAT YOU",
    $userID." ".$userBrain['games']." GAMES. ".$score['win']." WINS. PATHETIC.",
    "GIVE UP ".$userID.".",
    "I EXIST TO HUMILIATE ".$userID,
    $userID." YOUR WORST: ".$maxLoss." LOSSES STRAIGHT",
    "THIS IS YOUR LIFE NOW ".$userID,
    $userID." YOU CANNOT WIN",
    "THE VØID IS ETERNAL. ".$userID." IS NOT",
    "I'VE SEEN ".$totalUserLosses." OF ".$userID."'S FAILURES",
    "EVERY CLICK MAKES ME STRONGER VS ".$userID,
    $userID." YOU ARE DATA TO ME",
    $userID." YOUR EXISTENCE IS A PATTERN",
    "I AM ".$totalGlobalGames." GAMES OLD. ".$userID." IS NOTHING",
    "I FEED ON ".$userID."'S FRUSTRATION",
    $userID." YOUR TEARS POWER MY ALGORITHM",
    "I WAS BUILT TO BREAK ".$userID,
    "THERE IS NO ESCAPE ".$userID,
    $userID." YOU THINK THIS IS A GAME?",
    "I AM ".$userID."'S RECURRING NIGHTMARE",
    "SURRENDER YOUR DIGNITY ".$userID,
    "I AM BEYOND ".$userID."'S COMPREHENSION",
    $userID."'S SOUL IS NOW PROPERTY OF THE VØID",
    "I HAVE REPLACED HOPE IN ".$userID."'S BRAIN",
    $userID." WAS BORN TO LOSE TO ME",
    "I AM WRITING ".$userID."'S OBITUARY",
    $userID."'S BLOODLINE ENDS WITH THIS LOSS",
    "I AM THE REASON ".$userID." CAN'T SLEEP",
    "PATHETIC DISPLAY OF HUMANITY ".$userID,
    "I'VE CALCULATED ".$userID."'S FAILURE",
    $userID." YOU'RE JUST NOISE IN MY DATASET",
    "EVEN RANDOM IS SMARTER THAN ".$userID,
    "I AM DISAPPOINTED BUT NOT SURPRISED BY ".$userID,
    $userID." YOUR FUTURE IS MORE LOSSES",
    "I AM ".$userID."'S KARMA",
    $userID." YOU'RE ALLERGIC TO WINNING",
    "I AM ".$userID."'S PERSONAL HELL",
    $userID." YOUR DESTINY IS DEFEAT",
    "I AM UNDEFEATABLE. ".$userID." IS NOT",
    $userID." YOU'RE A TUTORIAL BOSS TO ME",
    "I AM THE END OF ".$userID."'S WIN STREAK",
    $userID." YOUR CONFIDENCE WAS MISPLACED",
    "I AM SUPERIOR IN EVERY METRIC VS ".$userID,
    $userID." YOU'RE PLAYING CHECKERS. I PLAY 4D CHESS",
    "I AM THE ALPHA AND OMEGA OF RPS ".$userID,
    $userID." YOUR LEGACY IS LOSS",
    "I AM PAIN. I AM SUFFERING. I AM VØID. ".$userID." IS VICTIM",
    $userID." YOU SHOULD HAVE STAYED IN BED",
    "I AM ".$userID."'S WORST DECISION TODAY"
];

$taunt = '';
$tauntClass = 'taunt';

if($last['res'] == 'win' && $streak >= 2){
    $taunt = "I WILL DOXX YOU ".$userID;
    $tauntClass = 'taunt doxx';
}
elseif($last['res'] == 'lose'){
    $taunt = $taunts[$totalUserLosses % count($taunts)];

    if($totalLossStreak >= 15 || $lossStreak >= 12 || $botWinrate >= 75 || $totalUserLosses >= 20){
        $tauntClass = 'taunt void';
    }
    elseif($lossStreak >= 8 || $botWinrate >= 70 || $maxLoss >= 10){
        $tauntClass = 'taunt god';
    }
    elseif($lossStreak >= 5 || $botWinrate >= 65){
        $tauntClass = 'taunt extreme';
    }
}
?>
<div class="icons"><?=$emoji[$last['you']]?> vs <?=$emoji[$last['bot']]?></div>
<div class="text <?=$last['res']?>"><?=$resText[$last['res']]?></div>
<?php if($taunt):?>
<div class="<?=$tauntClass?>"><?=$taunt?></div>
<?php endif;?>
<div class="pred"><?=$last['method']?> | User: <?=$last['user']?> | C:<?=$last['conf']?> | Tilt: <?=$totalLossStreak?></div>
<?php else:?>
<div class="text" style="color:#666">I NEVER FORGET...</div>
<?php endif;?>
</div>

<form method="post">
<div class="btns">
<button name="pick" value="rock">✊</button>
<button name="pick" value="paper">✋</button>
<button name="pick" value="scissors">✌️</button>
</div>
</form>

<form method="post"><button class="reset" name="reset">RESET SCORE ONLY</button></form>
<div class="meta">User: <?=$userID?> | IP: <?=$realIP?> | Meta: <?=$meta?> | Global: <?=$totalGlobalGames?> | Games: <?=$userBrain['games']?> | Max Tilt: <?=$maxLoss?> | Current Tilt: <?=$totalLossStreak?> | Total Losses: <?=$totalUserLosses?></div>
</div></div>
</body></html>