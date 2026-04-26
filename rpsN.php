<?php
session_start();
$file = 'rps_nightmare.txt';

if(!isset($_SESSION['score'])) $_SESSION['score'] = ['win'=>0,'lose'=>0,'draw'=>0];
if(!isset($_SESSION['last'])) $_SESSION['last'] = '';
if(!isset($_SESSION['h'])) $_SESSION['h'] = [];
if(!isset($_SESSION['t1'])) $_SESSION['t1'] = [];
if(!isset($_SESSION['t2'])) $_SESSION['t2'] = [];
if(!isset($_SESSION['t3'])) $_SESSION['t3'] = []; // 3rd-order: X|Y|Z ->?
if(!isset($_SESSION['meta'])) $_SESSION['meta'] = 0;
if(!isset($_SESSION['streak'])) $_SESSION['streak'] = 0;
if(!isset($_SESSION['lossStreak'])) $_SESSION['lossStreak'] = 0; // your loss streak for max tilt

if(file_exists($file)){
    $brain = json_decode(@file_get_contents($file), true);
    if($brain){
        $_SESSION['t1'] = $brain['t1']?? [];
        $_SESSION['t2'] = $brain['t2']?? [];
        $_SESSION['t3'] = $brain['t3']?? [];
        $_SESSION['meta'] = $brain['m']?? 0;
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
        $_SESSION['t1'][$p1][$you] = ($_SESSION['t1'][$p1][$you]?? 0) + 1;

        if(count($h) >= 2){
            $p2 = $h[count($h)-2];
            $k2 = $p2.'|'.$p1;
            $_SESSION['t2'][$k2][$you] = ($_SESSION['t2'][$k2][$you]?? 0) + 1;
        }

        if(count($h) >= 3){
            $p3 = $h[count($h)-3];
            $p2 = $h[count($h)-2];
            $k3 = $p3.'|'.$p2.'|'.$p1;
            $_SESSION['t3'][$k3][$you] = ($_SESSION['t3'][$k3][$you]?? 0) + 1;
        }

        // 1. Try 3rd-order first - highest accuracy
        if(count($h) >= 3){
            $k3 = $h[count($h)-3].'|'.$h[count($h)-2].'|'.$h[count($h)-1];
            if(!empty($_SESSION['t3'][$k3])){
                $next = $_SESSION['t3'][$k3];
                arsort($next);
                reset($next);
                $predict = key($next);
                $conf = $next[$predict];
                $method = '3rd-order';
            }
        }

        // 2. Fallback 2nd-order
        if($predict == 'none' && count($h) >= 2){
            $k2 = $h[count($h)-2].'|'.$h[count($h)-1];
            if(!empty($_SESSION['t2'][$k2])){
                $next = $_SESSION['t2'][$k2];
                arsort($next);
                reset($next);
                $predict = key($next);
                $conf = $next[$predict];
                $method = '2nd-order';
            }
        }

        // 3. Fallback 1st-order
        if($predict == 'none' &&!empty($_SESSION['t1'][$you])){
            $next = $_SESSION['t1'][$you];
            arsort($next);
            reset($next);
            $predict = key($next);
            $conf = $next[$predict];
            $method = '1st-order';
        }

        // 4. ANTI-PATTERN: A-B-A-B or A-A-A spam
        if(count($h) >= 4){
            $l4 = array_slice($h, -4);
            if($l4[0]==$l4[2] && $l4[1]==$l4[3] && $l4[0]!=$l4[1]){
                $predict = $l4[0];
                $conf = 99;
                $method = 'pattern-break';
            }
            // Detect spam: rock rock rock rock
            if(count(array_unique($l4)) == 1){
                $predict = $l4[0];
                $conf = 99;
                $method = 'anti-spam';
            }
        }

        // 5. ENTROPY CHECK: Are you playing random? If yes, counter frequency instead
        $entropy = 0;
        if(count($h) >= 10){
            $last10 = array_slice($h, -10);
            $counts = array_count_values($last10);
            foreach($counts as $c){
                $p = $c/10;
                $entropy -= $p * log($p, 2);
            }
        }
        // Max entropy for 3 choices is 1.585. If >1.4 you're basically random
        if($entropy > 1.4 && count($h) >= 10){
            $counts = array_count_values($h);
            arsort($counts);
            reset($counts);
            $predict = key($counts);
            $conf = 50;
            $method = 'entropy-counter';
        }

        // 6. META: Iocaine Powder levels
        if(isset($_SESSION['last']['bot']) && $counters[$_SESSION['last']['bot']] == $you){
            $_SESSION['meta']++;
        }else{
            $_SESSION['meta'] = max(0, $_SESSION['meta']-1);
        }

        // Level 0: predict your move, counter it
        // Level 1: predict you counter my counter, counter that
        // Level 2: predict you know I know, counter that
        if($_SESSION['meta'] > 2 && $predict!= 'none'){
            $predict = $counters[$predict]; // level 1
        }
        if($_SESSION['meta'] > 4 && $predict!= 'none'){
            $predict = $counters[$predict]; // level 2
            $method = 'iocaine-2';
        }

        // 7. DECIDE
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
        $bot = 'paper'; // 35% pick rock first
    }

    // 8. Only 5% chaos now. Less randomness = more brutal
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

    @file_put_contents($file, json_encode([
        't1' => $_SESSION['t1'],
        't2' => $_SESSION['t2'],
        't3' => $_SESSION['t3'],
        'm' => $_SESSION['meta']
    ]));

    $_SESSION['last'] = ['you'=>$you,'bot'=>$bot,'res'=>$result,'pred'=>$predict,'conf'=>$conf,'method'=>$method];
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

if(isset($_POST['reset'])){
    $_SESSION['score'] = ['win'=>0,'lose'=>0,'draw'=>0];
    $_SESSION['last'] = '';
    $_SESSION['h'] = [];
    $_SESSION['t1'] = [];
    $_SESSION['t2'] = [];
    $_SESSION['t3'] = [];
    $_SESSION['meta'] = 0;
    $_SESSION['streak'] = 0;
    $_SESSION['lossStreak'] = 0;
    @unlink($file);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

$score = $_SESSION['score'];
$last = $_SESSION['last'];
$total = $score['win'] + $score['lose'] + $score['draw'];
$winrate = $total > 0? round($score['win']/$total*100) : 0;
$botWinrate = $total > 0? round($score['lose']/$total*100) : 0;
$streak = $_SESSION['streak'];
$lossStreak = $_SESSION['lossStreak'];
?>
<!DOCTYPE html><html><head><meta name="viewport" content="width=device-width,1,maximum-scale=1">
<title>RPS NIGHTMARE</title><style>
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
<h1>RPS NIGHTMARE</h1>
<div class="sub">3RD-ORDER MARKOV + IOCAINE + ENTROPY</div>
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
$taunts_med = ["I'M IN YOUR HEAD","ADAPT. YOU CAN'T.","YOUR PATTERNS ARE MINE","MARKOV > MID","3RD-ORDER DIFF","GET FINGERPRINTED"];
$taunts_extreme = ["IS THAT YOUR FINAL FORM?","BOT WR% > YOUR IQ","I LEARNED YOU IN 3 MOVES","EMOTIONAL DAMAGE?","KEEP CRYING + COPE + SEETHE","SKILL ISSUE","FF@15","L + RATIO + NO MAIDENS","UNINSTALL","WOMP WOMP"];
$taunts_god = ["ALT+F4 RIGHT NOW","TOUCH GRASS NERD","I AM THE 0.1%","GOOGLE 'HOW TO WIN RPS'","DELETE SYSTEM32","I READ YOUR SOUL","TOO EASY. NEXT.","YOUR BRAIN IS OPEN SOURCE","I SIMULATED 1000 GAMES VS YOU","YOU'RE IN A BOTNET"];

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
<div class="pred"><?=$last['method']?> | Pred: <?=$last['pred']?> | C:<?=$last['conf']?> | L-Streak: <?=$lossStreak?></div>
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

<form method="post"><button class="reset" name="reset">WIPE BRAIN & RESET</button></form>
<div class="meta">Meta: <?=$_SESSION['meta']?> | Moves: <?=count($_SESSION['h'])?>/200 | W-Streak: <?=$streak?> | L-Streak: <?=$lossStreak?></div>
</div></div>
</body></html>