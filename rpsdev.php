<?php
session_start();

// 3 FILES:
// 1. rps_global_brain.txt - NEVER RESETS. Permanent devil brain. Used for calculations only.
// 2. rps_users.txt - Tracks specific opponents by IP or username
// 3. rps_session.txt - Gets wiped on reset. Only stores current session score

$globalFile = 'rps_global_brain.txt';
$usersFile = 'rps_users.txt';
$sessionFile = 'rps_session.txt';

// Get user ID - use IP or let user set name
$userID = $_GET['u']?? $_SERVER['REMOTE_ADDR']?? 'anon';
$userID = preg_replace('/[^a-zA-Z0-9]/', '', $userID); // sanitize

if(!isset($_SESSION['score'])) $_SESSION['score'] = ['win'=>0,'lose'=>0,'draw'=>0];
if(!isset($_SESSION['last'])) $_SESSION['last'] = '';
if(!isset($_SESSION['h'])) $_SESSION['h'] = [];
if(!isset($_SESSION['streak'])) $_SESSION['streak'] = 0;
if(!isset($_SESSION['lossStreak'])) $_SESSION['lossStreak'] = 0;

// Load GLOBAL BRAIN - never resets, only for calculations
$globalBrain = ['t1'=>[],'t2'=>[],'t3'=>[],'m'=>0];
if(file_exists($globalFile)){
    $globalBrain = json_decode(@file_get_contents($globalFile), true)?: $globalBrain;
}

// Load USER-SPECIFIC BRAIN - tracks this opponent only
$allUsers = [];
if(file_exists($usersFile)){
    $allUsers = json_decode(@file_get_contents($usersFile), true)?: [];
}
$userBrain = $allUsers[$userID]?? ['t1'=>[],'t2'=>[],'t3'=>[],'m'=>0,'games'=>0];

// Merge brains for prediction: global + user-specific
$t1 = $globalBrain['t1'];
$t2 = $globalBrain['t2'];
$t3 = $globalBrain['t3'];
$meta = max($globalBrain['m'], $userBrain['m']);

// Merge user data into global for better prediction
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
        // Update USER brain
        $userBrain['t1'][$p1][$you] = ($userBrain['t1'][$p1][$you]?? 0) + 1;
        // Update GLOBAL brain
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

        // PREDICTION using merged t1/t2/t3 from above
        if(count($h) >= 3){
            $k3 = $h[count($h)-3].'|'.$h[count($h)-2].'|'.$h[count($h)-1];
            if(!empty($t3[$k3])){
                $next = $t3[$k3];
                arsort($next);
                reset($next);
                $predict = key($next);
                $conf = $next[$predict];
                $method = '3rd-devil';
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
                $method = '2nd-devil';
            }
        }

        if($predict == 'none' &&!empty($t1[$you])){
            $next = $t1[$you];
            arsort($next);
            reset($next);
            $predict = key($next);
            $conf = $next[$predict];
            $method = '1st-devil';
        }

        // Anti-pattern + spam
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

        // Entropy check
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
            $method = 'entropy-devil';
        }

        // Meta Iocaine
        if(isset($_SESSION['last']['bot']) && $counters[$_SESSION['last']['bot']] == $you){
            $meta++;
        }else{
            $meta = max(0, $meta-1);
        }
        $userBrain['m'] = $meta;
        $globalBrain['m'] = max($globalBrain['m'], $meta);

        if($meta > 2 && $predict!= 'none') $predict = $counters[$predict];
        if($meta > 4 && $predict!= 'none'){
            $predict = $counters[$predict];
            $method = 'iocaine-2';
        }

        if($predict!= 'none' && $conf > 0){
            $bot = $counters[$predict];
        }else{
            if(!empty($h)){
                $counts = array_count_values($h);
                arsort($counts);
                reset($counts);
                $bot = $counters[key($counts)];
            }
        }
    }else{
        $bot = 'paper';
    }

    if(rand(1,100) <= 5) $bot = $choices[array_rand($choices)];

    if($you == $bot){
        $result = 'draw';
        $_SESSION['score']['draw']++;
        $_SESSION['streak'] = 0;
        $_SESSION['lossStreak'] = 0;
    }elseif($counters[$you] == $bot){
        $result = 'lose';
        $_SESSION['score']['lose']++;
        $_SESSION['streak']++;
        $_SESSION['lossStreak']++;
    }else{
        $result = 'win';
        $_SESSION['score']['win']++;
        $_SESSION['streak'] = 0;
        $_SESSION['lossStreak'] = 0;
    }

    $_SESSION['h'][] = $you;
    if(count($_SESSION['h']) > 200) array_shift($_SESSION['h']);
    $userBrain['games']++;

    // SAVE GLOBAL BRAIN - NEVER RESETS
    @file_put_contents($globalFile, json_encode($globalBrain));

    // SAVE USER BRAIN - tracks this opponent
    $allUsers[$userID] = $userBrain;
    @file_put_contents($usersFile, json_encode($allUsers));

    $_SESSION['last'] = ['you'=>$you,'bot'=>$bot,'res'=>$result,'pred'=>$predict,'conf'=>$conf,'method'=>$method,'user'=>$userID];
    header("Location: ".$_SERVER['PHP_SELF']."?u=".$userID);
    exit;
}

if(isset($_POST['reset'])){
    // ONLY RESETS SESSION SCORE. BRAINS STAY.
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
$totalGlobalGames = array_sum(array_column($allUsers, 'games'));
?>
<!DOCTYPE html><html><head><meta name="viewport" content="width=device-width,1,maximum-scale=1">
<title>RPS DEVIL</title><style>
:root{--neon:#ff073a;--glow:0 0 5px var(--neon),0 0 15px var(--neon)}
@keyframes neonmove{0%{box-shadow:-2px 0 10px var(--neon)}25%{box-shadow:0 -2px 10px var(--neon)}50%{box-shadow:2px 0 10px var(--neon)}75%{box-shadow:0 2px 10px var(--neon)}100%{box-shadow:-2px 0 10px var(--neon)}}
@keyframes pop{0%{transform:scale(0.5);opacity:0}100%{transform:scale(1);opacity:1}}
@keyframes float{0%{transform:translate(0,0)}50%{transform:translate(20px,-30px)}100%{transform:translate(0,0)}}
@keyframes shake{0%,100%{transform:translateX(0)}10%,30%,50%,70%,90%{transform:translateX(-5px)}20%,40%,60%,80%{transform:translateX(5px)}}
*{box-sizing:border-box;margin:0}
body{background:#000;color:#e9edef;font-family:system-ui;display:flex;justify-content:center;align-items:center;min-height:100vh;padding:20px}
.bg{position:fixed;inset:0;z-index:0;pointer-events:none}
.bubble-bg{position:absolute;border-radius:50%;background:radial-gradient(circle, rgba(255,7,58,0.15) 0%, transparent 70%);box-shadow:0 0 50px rgba(255,7,58,0.25);animation:float 8s ease-in-out infinite}
.b1{width:220px;height:220px;top:10%;left:-70px}
.b2{width:160px;height:160px;bottom:15%;right:-50px;animation-delay:2s}
.wrap{width:100%;max-width:420px;z-index:1}
.card{background:#111;border:2px solid var(--neon);border-radius:16px;padding:24px;text-align:center;animation:neonmove 3s linear infinite}
.back{position:fixed;top:12px;left:12px;padding:6px 12px;background:#000;border:2px solid var(--neon);border-radius:8px;color:var(--neon);font-weight:700;cursor:pointer;font-size:11px;text-decoration:none;transition:.2s;z-index:999}
.back:hover{background:var(--neon);color:#000;box-shadow:var(--glow)}
h1{font-size:20px;margin-bottom:4px;text-shadow:var(--glow)}
.sub{font-size:11px;color:#ff5c7a;margin-bottom:12px;letter-spacing:1px}
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin:16px 0;padding:12px;background:#0a0a0a;border-radius:10px;border:1px solid var(--neon)}
.stats div{font-size:11px}.stats b{color:var(--neon);font-size:16px;display:block;text-shadow:0 0 5px var(--neon)}
.result{min-height:120px;margin:20px 0;display:flex;flex-direction:column;justify-content:center;align-items:center}
.result.show{animation:pop.3s}
.result.icons{font-size:40px;margin-bottom:6px}
.result.text{font-size:20px;font-weight:700;text-shadow:var(--glow)}
.result.pred{font-size:9px;color:#666;margin-top:4px}
.taunt{font-size:12px;color:#ff073a;margin-top:8px;text-shadow:0 0 10px #ff073a;letter-spacing:1px;font-weight:900;animation:shake.5s}
.taunt.extreme{font-size:13px;color:#fff;background:#ff073a;padding:5px 10px;border-radius:6px;animation:shake.4s infinite}
.taunt.god{font-size:15px;color:#000;background:#fff;padding:6px 12px;border-radius:6px;animation:shake.2s infinite;font-weight:900}
.win{color:#00ff88}.lose{color:#ff5c7a}.draw{color:#ffaa00}
.btns{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin:20px 0}
.btns button{background:#1a1a1a;border:2px solid var(--neon);border-radius:12px;padding:16px;font-size:32px;cursor:pointer;transition:.2s;animation:neonmove 4s linear infinite}
.btns button:active{transform:scale(0.95)}
.reset{width:100%;padding:12px;background:transparent;border:2px solid var(--neon);border-radius:10px;color:var(--neon);font-weight:700;cursor:pointer;margin-top:12px;transition:.2s}
.reset:hover{background:var(--neon);color:#000;box-shadow:var(--glow)}
.meta{font-size:9px;color:#ff5c7a;margin-top:8px}
</style></head><body>
<a href="index.php" class="back">← BACK</a>
<div class="bg"><div class="bubble-bg b1"></div><div class="bubble-bg b2"></div></div>
<div class="wrap"><div class="card">
<h1>RPS DEVIL</h1>
<div class="sub">GLOBAL BRAIN + USER PROFILING</div>
<div class="stats">
  <div>YOU<b><?=$score['win']?></b></div>
  <div>DRAW<b><?=$score['draw']?></b></div>
  <div>BOT<b><?=$score['lose']?></b></div>
  <div>BOT WR%<b><?=$botWinrate?></b></div>
</div>

<div class="result <?=$last?'show':''?>">
<?php if($last):
$emoji = ['rock'=>'✊','paper'=>'✋','scissors'=>'✌️'];
$resText = ['win'=>'YOU WIN','lose'=>'BOT WINS','draw'=>'DRAW'];

$taunts_mild = ["CALCULATED.","PREDICTABLE.","I SEE YOU.","DOWN BAD.","BOT DIFF.","READ LIKE A BOOK."];
$taunts_med = ["I'M IN YOUR HEAD","ADAPT. YOU CAN'T.","YOUR PATTERNS ARE MINE","MARKOV > MID","3RD-ORDER DIFF","GET FINGERPRINTED","I KNOW YOUR IP","DEVIL BRAIN ACTIVE"];
$taunts_extreme = ["IS THAT YOUR FINAL FORM?","BOT WR% > YOUR IQ","I LEARNED YOU IN 3 MOVES","EMOTIONAL DAMAGE?","KEEP CRYING + COPE + SEETHE","SKILL ISSUE","FF@15","L + RATIO + NO MAIDENS","UNINSTALL","WOMP WOMP"];
$taunts_god = ["ALT+F4 RIGHT NOW","TOUCH GRASS NERD","I AM THE 0.1%","GOOGLE 'HOW TO WIN RPS'","DELETE SYSTEM32","I READ YOUR SOUL","TOO EASY. NEXT.","YOUR BRAIN IS OPEN SOURCE","I SIMULATED 1000 GAMES VS YOU","YOU'RE IN A BOTNET","I KNOW USER: <?=$userID?>"];

$taunt = '';
$tauntClass = 'taunt';
if($last['res'] == 'lose'){
    if($lossStreak >= 8 || $botWinrate >= 70){
        $taunt = $taunts_god[array_rand($taunts_god)];
        $tauntClass = 'taunt god';
    }elseif($lossStreak >= 5 || $botWinrate >= 65){
        $taunt = $taunts_extreme[array_rand($taunts_extreme)];
        $tauntClass = 'taunt extreme';
    }elseif($lossStreak >= 3 || $botWinrate >= 55){
        $taunt = $taunts_med[array_rand($taunts_med)];
    }elseif($score['lose'] > $score['win']){
        $taunt = $taunts_mild[array_rand($taunts_mild)];
    }
}
?>
<div class="icons"><?=$emoji[$last['you']]?> vs <?=$emoji[$last['bot']]?></div>
<div class="text <?=$last['res']?>"><?=$resText[$last['res']]?></div>
<?php if($taunt):?>
<div class="<?=$tauntClass?>"><?=$taunt?></div>
<?php endif;?>
<div class="pred"><?=$last['method']?> | User: <?=$last['user']?> | C:<?=$last['conf']?></div>
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
<div class="meta">User: <?=$userID?> | Meta: <?=$meta?> | Global Games: <?=$totalGlobalGames?> | Your Games: <?=$userBrain['games']?></div>
</div></div>
</body></html>