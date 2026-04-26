<?php
session_start();
$roomsDir = 'rooms/';
if(!is_dir($roomsDir)) mkdir($roomsDir, 0777, true);

$room = $_GET['r'] ?? '';
$user = $_GET['u'] ?? '';

// AJAX: Get state
if(isset($_GET['state']) && $room){
    header('Content-Type: application/json');
    header('Cache-Control: no-cache');
    $f = $roomsDir . $room . '.json';
    echo file_exists($f) ? file_get_contents($f) : '{"error":1}';
    exit;
}

// CREATE
if(isset($_POST['create'])){
    $name = preg_replace('/[^a-zA-Z0-9]/', '', $_POST['name'] ?? 'p1');
    $id = substr(md5(microtime()), 0, 6);
    file_put_contents($roomsDir.$id.'.json', json_encode([
        'p1' => $name, 'p2' => '', 'p1s' => 0, 'p2s' => 0,
        'p1m' => '', 'p2m' => '', 'round' => 1, 'last' => null
    ]));
    header("Location: ?r=$id&u=$name");
    exit;
}

// JOIN - FIX: Don't check against GET user, just check if p2 empty
if($room && isset($_POST['join'])){
    $name = preg_replace('/[^a-zA-Z0-9]/', '', $_POST['name'] ?? 'p2');
    $f = $roomsDir . $room . '.json';
    if(file_exists($f)){
        $d = json_decode(file_get_contents($f), 1);
        if($d && !$d['p2'] && $d['p1'] != $name){
            $d['p2'] = $name;
            file_put_contents($f, json_encode($d));
        }
        header("Location: ?r=$room&u=$name");
        exit;
    }
}

// PICK
if($room && isset($_POST['pick'])){
    $f = $roomsDir . $room . '.json';
    if(file_exists($f)){
        $d = json_decode(file_get_contents($f), 1);
        $isP1 = $d['p1'] == $user;
        $isP2 = $d['p2'] == $user;
        if($isP1 && !$d['p1m']) $d['p1m'] = $_POST['pick'];
        if($isP2 && !$d['p2m']) $d['p2m'] = $_POST['pick'];
        
        if($d['p1m'] && $d['p2m']){
            $w = ['rock'=>'scissors','paper'=>'rock','scissors'=>'paper'];
            if($d['p1m'] == $d['p2m']) $r = 'draw';
            elseif($w[$d['p1m']] == $d['p2m']) {$r='p1'; $d['p1s']++;}
            else {$r='p2'; $d['p2s']++;}
            $d['last'] = ['p1'=>$d['p1m'],'p2'=>$d['p2m'],'r'=>$r];
            $d['p1m'] = $d['p2m'] = '';
            $d['round']++;
        }
        file_put_contents($f, json_encode($d));
    }
    header("Location: ?r=$room&u=$user");
    exit;
}

$d = $room && file_exists($roomsDir.$room.'.json') ? json_decode(file_get_contents($roomsDir.$room.'.json'),1) : null;
$isP1 = $d && $d['p1'] == $user;
$isP2 = $d && $d['p2'] == $user;
$inGame = $isP1 || $isP2;
$yourMove = $isP1 ? $d['p1m'] : ($isP2 ? $d['p2m'] : '');
$waitMove = $inGame && $yourMove && (!$d['p1m'] || !$d['p2m']);

$link = $room ? 'http'.(!empty($_SERVER['HTTPS'])?'s':'').'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?r=$room" : '';
?>
<!DOCTYPE html><html><head><meta name="viewport" content="width=device-width,1,maximum-scale=1">
<title>RPS PVP</title><style>
:root{--n:#ff073a;--g:0 0 5px var(--n),0 0 15px var(--n)}
@keyframes n{0%{box-shadow:-2px 0 10px var(--n)}25%{box-shadow:0 -2px 10px var(--n)}50%{box-shadow:2px 0 10px var(--n)}75%{box-shadow:0 2px 10px var(--n)}100%{box-shadow:-2px 0 10px var(--n)}}
@keyframes p{0%,100%{opacity:1}50%{opacity:0.4}}
*{box-sizing:border-box;margin:0}
body{background:#000;color:#e9edef;font-family:system-ui;display:flex;justify-content:center;align-items:center;min-height:100vh;padding:20px}
.w{width:100%;max-width:420px}
.c{background:#111;border:2px solid var(--n);border-radius:16px;padding:24px;text-align:center;animation:n 3s linear infinite}
h1{font-size:22px;margin-bottom:8px;text-shadow:var(--g);letter-spacing:2px;color:var(--n)}
.s{font-size:10px;color:#ff5c7a;margin-bottom:16px;letter-spacing:2px}
.p{display:grid;grid-template-columns:1fr auto 1fr;gap:12px;margin:16px 0;padding:12px;background:#0a0a0a;border-radius:10px;border:1px solid var(--n);align-items:center}
.p .u{font-size:12px}
.p .u b{display:block;color:var(--n);font-size:18px;text-shadow:0 0 5px var(--n)}
.v{font-size:20px;color:#666;font-weight:900}
.r{min-height:120px;margin:20px 0;display:flex;flex-direction:column;justify-content:center;align-items:center}
.r .i{font-size:40px;margin-bottom:6px}
.r .t{font-size:18px;font-weight:700;text-shadow:var(--g)}
.wait{color:var(--n);animation:p 1.5s infinite;font-size:14px;font-weight:900}
.b{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin:20px 0}
.b button{background:#1a1a1a;border:2px solid var(--n);border-radius:12px;padding:16px;font-size:32px;cursor:pointer}
.b button:disabled{opacity:0.3}
.a{width:100%;padding:12px;background:var(--n);border:none;border-radius:10px;color:#000;font-weight:700;cursor:pointer;margin-top:12px;font-size:14px}
.l{padding:20px 0}
.l input{width:100%;padding:12px;background:#0a0a0a;border:2px solid var(--n);border-radius:10px;color:#e9edef;text-align:center;font-size:14px;margin-bottom:12px}
.k{font-size:11px;background:#0a0a0a;padding:12px;border-radius:8px;border:1px solid var(--n);word-break:break-all;margin:12px 0;cursor:pointer}
.k b{color:var(--n);display:block;margin-top:4px}
.k .cp{text-align:center;font-size:9px;color:#ff5c7a;margin-top:6px}
</style>
<script>
function cp(el,t){
  const ta=document.createElement('textarea');
  ta.value=t;ta.style.position='fixed';ta.style.left='-9999px';
  document.body.appendChild(ta);ta.select();
  try{document.execCommand('copy');el.querySelector('.cp').textContent='COPIED!'}
  catch(e){alert('Copy failed')}
  document.body.removeChild(ta);
  setTimeout(()=>{el.querySelector('.cp').textContent='TAP TO COPY'},1500);
}

// NUCLEAR OPTION: Reload every 1.5s if in game
<?php if($room && $inGame):?>
setInterval(()=>{location.reload()},1500);
<?php endif;?>
</script>
</head><body>
<div class="w"><div class="c">
<h1>RPS PVP</h1>
<div class="s">PLAYER VS PLAYER</div>

<?php if(!$room):?>
<div class="l">
<form method="post">
<input type="text" name="name" placeholder="YOUR NAME" maxlength="16" required>
<button class="a" name="create">CREATE ROOM</button>
</form>
</div>

<?php elseif(!$d):?>
<div class="l">
<p style="color:#ff073a;margin-bottom:12px">ROOM NOT FOUND</p>
<a href="?" class="a" style="display:block;text-decoration:none">BACK</a>
</div>

<?php elseif(!$inGame && !$d['p2']):?>
<div class="l">
<p style="margin-bottom:12px">JOIN GAME WITH <b style="color:var(--n)"><?=$d['p1']?></b></p>
<form method="post">
<input type="text" name="name" placeholder="YOUR NAME" maxlength="16" required>
<button class="a" name="join">JOIN ROOM</button>
</form>
</div>

<?php elseif(!$inGame):?>
<div class="l">
<p style="color:#ff073a">ROOM IS FULL</p>
<a href="?" class="a" style="display:block;text-decoration:none;margin-top:12px">BACK</a>
</div>

<?php else:?>
<div class="p">
  <div class="u"><?=$d['p1']?><b><?=$d['p1s']?></b></div>
  <div class="v">VS</div>
  <div class="u"><?=$d['p2'] ?: 'WAITING...'?><b><?=$d['p2s']?></b></div>
</div>

<?php if(!$d['p2']):?>
<div class="k" onclick="cp(this,'<?=$link?>')">
SHARE THIS LINK:
<b><?=$link?></b>
<div class="cp">TAP TO COPY</div>
</div>
<div class="wait">WAITING FOR OPPONENT...</div>

<?php elseif($waitMove):?>
<div class="wait">WAITING FOR <?=$isP1 ? $d['p2'] : $d['p1']?>...</div>

<?php elseif($d['last']):?>
<div class="r">
<div class="i">
<?php $e=['rock'=>'✊','paper'=>'✋','scissors'=>'✌️']; echo $e[$d['last']['p1']].' vs '.$e[$d['last']['p2']]; ?>
</div>
<div class="t">
<?php if($d['last']['r']=='draw')echo'DRAW';elseif($d['last']['r']=='p1')echo$d['p1'].' WINS';else echo$d['p2'].' WINS';?>
</div>
</div>

<?php else:?>
<div class="r">
<div class="t" style="color:#666">ROUND <?=$d['round']?><br>MAKE YOUR MOVE</div>
</div>
<?php endif;?>

<?php if($d['p2'] && !$yourMove && !$waitMove):?>
<form method="post">
<div class="b">
<button name="pick" value="rock">✊</button>
<button name="pick" value="paper">✋</button>
<button name="pick" value="scissors">✌️</button>
</div>
</form>
<?php elseif($yourMove):?>
<div class="b">
<button disabled>✊</button>
<button disabled>✋</button>
<button disabled>✌️</button>
</div>
<?php endif;?>

<?php endif;?>

</div></div>
</body></html>