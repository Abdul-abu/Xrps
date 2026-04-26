<?php
session_start();

if(!isset($_SESSION['score'])) $_SESSION['score'] = ['win'=>0,'lose'=>0,'draw'=>0];
if(!isset($_SESSION['last'])) $_SESSION['last'] = '';
if(!isset($_SESSION['history'])) $_SESSION['history'] = [];

if(isset($_POST['pick'])){
    $choices = ['rock','paper','scissors'];
    $you = $_POST['pick'];

    // SMART BOT LOGIC
    $history = $_SESSION['history'];
    $bot = 'rock'; // default

    if(count($history) >= 3){
        // 1. Check if user repeats moves: rock rock rock
        $last3 = array_slice($history, -3);
        if($last3[0] == $last3[1] && $last3[1] == $last3[2]){
            // User likes repeating, counter their last move
            $counters = ['rock'=>'paper','paper'=>'scissors','scissors'=>'rock'];
            $bot = $counters[$last3[2]];
        }
        // 2. Check if user cycles: rock paper scissors
        elseif($last3 == ['rock','paper','scissors']){
            $bot = 'rock'; // beats scissors, which would be next
        }
        // 3. Frequency analysis: pick what beats their most used move
        else{
            $counts = array_count_values($history);
            arsort($counts);
            $mostUsed = array_key_first($counts);
            $counters = ['rock'=>'paper','paper'=>'scissors','scissors'=>'rock'];
            $bot = $counters[$mostUsed];
        }
    }else{
        // Not enough data, go random but slightly favor paper
        $weights = ['rock'=>30,'paper'=>40,'scissors'=>30];
        $rand = rand(1,100);
        if($rand <= 30) $bot = 'rock';
        elseif($rand <= 70) $bot = 'paper';
        else $bot = 'scissors';
    }

    // Add 20% randomness so bot isn't predictable
    if(rand(1,100) <= 20) $bot = $choices[array_rand($choices)];

    if($you == $bot){
        $result = 'draw';
        $_SESSION['score']['draw']++;
    }elseif(
        ($you=='rock' && $bot=='scissors') ||
        ($you=='paper' && $bot=='rock') ||
        ($you=='scissors' && $bot=='paper')
    ){
        $result = 'win';
        $_SESSION['score']['win']++;
    }else{
        $result = 'lose';
        $_SESSION['score']['lose']++;
    }

    $_SESSION['history'][] = $you;
    if(count($_SESSION['history']) > 20) array_shift($_SESSION['history']); // keep last 20 moves only

    $_SESSION['last'] = ['you'=>$you,'bot'=>$bot,'res'=>$result];
    header("Location: rps.php");
    exit;
}

if(isset($_POST['reset'])){
    $_SESSION['score'] = ['win'=>0,'lose'=>0,'draw'=>0];
    $_SESSION['last'] = '';
    $_SESSION['history'] = [];
    header("Location: rps.php");
    exit;
}

$score = $_SESSION['score'];
$last = $_SESSION['last'];
?>
<!DOCTYPE html><html><head><meta name="viewport" content="width=device-width,1,maximum-scale=1">
<title>RPS AI</title><style>
:root{--neon:#ff073a;--glow:0 0 5px var(--neon),0 0 15px var(--neon)}
@keyframes neonmove{0%{box-shadow:-2px 0 10px var(--neon)}25%{box-shadow:0 -2px 10px var(--neon)}50%{box-shadow:2px 0 10px var(--neon)}75%{box-shadow:0 2px 10px var(--neon)}100%{box-shadow:-2px 0 10px var(--neon)}}
@keyframes pop{0%{transform:scale(0.5);opacity:0}100%{transform:scale(1);opacity:1}}
@keyframes float{0%{transform:translate(0,0)}50%{transform:translate(20px,-30px)}100%{transform:translate(0,0)}}
*{box-sizing:border-box;margin:0}
body{background:#000;color:#e9edef;font-family:system-ui;display:flex;justify-content:center;align-items:center;min-height:100vh;padding:20px}
.bg{position:fixed;inset:0;z-index:0;pointer-events:none}
.bubble-bg{position:absolute;border-radius:50%;background:radial-gradient(circle, rgba(255,7,58,0.12) 0%, transparent 70%);box-shadow:0 0 50px rgba(255,7,58,0.2);animation:float 8s ease-in-out infinite}
.b1{width:220px;height:220px;top:10%;left:-70px}
.b2{width:160px;height:160px;bottom:15%;right:-50px;animation-delay:2s}
.wrap{width:100%;max-width:400px;z-index:1}
.card{background:#111;border:2px solid var(--neon);border-radius:16px;padding:24px;text-align:center;animation:neonmove 3s linear infinite}
.back{position:fixed;top:12px;left:12px;padding:6px 12px;background:#000;border:2px solid var(--neon);border-radius:8px;color:var(--neon);font-weight:700;cursor:pointer;font-size:11px;text-decoration:none;transition:.2s;z-index:999}
.back:hover{background:var(--neon);color:#000;box-shadow:var(--glow)}
h1{font-size:22px;margin-bottom:4px;text-shadow:var(--glow)}
.sub{font-size:12px;color:#ff5c7a;margin-bottom:12px}
.stats{display:flex;justify-content:space-around;margin:16px 0;padding:12px;background:#0a0a0a;border-radius:10px;border:1px solid var(--neon)}
.stats div{font-size:14px}.stats b{color:var(--neon);font-size:18px;display:block;text-shadow:0 0 5px var(--neon)}
.result{min-height:80px;margin:20px 0;display:flex;flex-direction:column;justify-content:center;align-items:center}
.result.show{animation:pop.3s}
.result.icons{font-size:40px;margin-bottom:8px}
.result.text{font-size:20px;font-weight:700;text-shadow:var(--glow)}
.win{color:#00ff88}.lose{color:#ff5c7a}.draw{color:#ffaa00}
.btns{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin:20px 0}
.btns button{background:#1a1a1a;border:2px solid var(--neon);border-radius:12px;padding:16px;font-size:32px;cursor:pointer;transition:.2s;animation:neonmove 4s linear infinite}
.btns button:hover{transform:scale(1.1);box-shadow:0 0 15px var(--neon),0 0 30px var(--neon)}
.reset{width:100%;padding:12px;background:transparent;border:2px solid var(--neon);border-radius:10px;color:var(--neon);font-weight:700;cursor:pointer;margin-top:12px;transition:.2s}
.reset:hover{background:var(--neon);color:#000;box-shadow:var(--glow)}
</style></head><body>
<a href="index.php" class="back">← BACK</a>
<div class="bg"><div class="bubble-bg b1"></div><div class="bubble-bg b2"></div></div>
<div class="wrap"><div class="card">
<h1>ROCK PAPER SCISSORS</h1>
<div class="sub">SMART BOT ENABLED</div>
<div class="stats">
  <div>WIN<b><?=$score['win']?></b></div>
  <div>DRAW<b><?=$score['draw']?></b></div>
  <div>LOSE<b><?=$score['lose']?></b></div>
</div>

<div class="result <?=$last?'show':''?>">
<?php if($last):
$emoji = ['rock'=>'✊','paper'=>'✋','scissors'=>'✌️'];
$resText = ['win'=>'YOU WIN','lose'=>'BOT WINS','draw'=>'DRAW'];
?>
<div class="icons"><?=$emoji[$last['you']]?> vs <?=$emoji[$last['bot']]?></div>
<div class="text <?=$last['res']?>"><?=$resText[$last['res']]?></div>
<?php else:?>
<div class="text" style="color:#666">BOT IS WATCHING...</div>
<?php endif;?>
</div>

<form method="post">
<div class="btns">
<button name="pick" value="rock">✊</button>
<button name="pick" value="paper">✋</button>
<button name="pick" value="scissors">✌️</button>
</div>
</form>

<form method="post"><button class="reset" name="reset">RESET</button></form>
</div></div>
</body></html>