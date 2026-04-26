<?php
$globalFile = 'rps_global_brain.txt';
$usersFile = 'rps_users.txt';

$global = ['t1'=>[],'t2'=>[],'t3'=>[],'m'=>0];
$users = [];

if(file_exists($globalFile)) {
    $global = json_decode(@file_get_contents($globalFile), true) ?: $global;
}
if(file_exists($usersFile)) {
    $users = json_decode(@file_get_contents($usersFile), true) ?: [];
}

// Totals
$totalGlobalGames = array_sum(array_column($users, 'games'));
$totalUsers = count($users);

// Top 3rd-order patterns: X|Y|Z -> most common next move
$top3 = [];
foreach($global['t3'] as $seq => $moves) {
    arsort($moves);
    reset($moves);
    $topMove = key($moves);
    $count = $moves[$topMove];
    $top3[$seq] = ['move' => $topMove, 'count' => $count];
}
arsort($top3);
$top3 = array_slice($top3, 0, 10, true);

// Top 2nd-order patterns: X|Y -> next
$top2 = [];
foreach($global['t2'] as $seq => $moves) {
    arsort($moves);
    reset($moves);
    $topMove = key($moves);
    $count = $moves[$topMove];
    $top2[$seq] = ['move' => $topMove, 'count' => $count];
}
arsort($top2);
$top2 = array_slice($top2, 0, 10, true);

// Global move frequency
$freq = ['rock'=>0,'paper'=>0,'scissors'=>0];
foreach($global['t1'] as $lastMove => $moves) {
    foreach($moves as $move => $count) {
        $freq[$move] += $count;
    }
}
$totalMoves = array_sum($freq);

// Top users by games - anonymized
uasort($users, fn($a,$b) => ($b['games']??0) <=> ($a['games']??0));
$topUsers = array_slice($users, 0, 10, true);
?>
<!DOCTYPE html><html><head><meta name="viewport" content="width=device-width,1,maximum-scale=1">
<title>VØID STATS</title><style>
:root{--neon:#ff073a;--glow:0 0 5px var(--neon),0 0 15px var(--neon)}
*{box-sizing:border-box;margin:0}
body{background:#000;color:#e9edef;font-family:system-ui;padding:20px}
.back{position:fixed;top:12px;left:12px;padding:6px 12px;background:#000;border:2px solid var(--neon);border-radius:8px;color:var(--neon);font-weight:700;font-size:11px;text-decoration:none;z-index:999}
.back:hover{background:var(--neon);color:#000;box-shadow:var(--glow)}
.wrap{max-width:800px;margin:40px auto}
h1{font-size:24px;text-shadow:var(--glow);text-align:center;margin-bottom:8px}
.sub{font-size:11px;color:#ff5c7a;text-align:center;margin-bottom:24px;letter-spacing:1px}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:16px;margin-bottom:20px}
.card{background:#111;border:2px solid var(--neon);border-radius:12px;padding:16px}
.card h2{font-size:14px;color:var(--neon);margin-bottom:12px;text-shadow:0 0 5px var(--neon)}
.stat{display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #222;font-size:12px}
.stat:last-child{border-bottom:0}
.stat b{color:var(--neon)}
table{width:100%;border-collapse:collapse;font-size:11px}
th,td{padding:6px;text-align:left;border-bottom:1px solid #222}
th{color:var(--neon);font-size:10px}
td b{color:var(--neon)}
.bar{height:4px;background:#222;border-radius:2px;margin-top:2px;overflow:hidden}
.bar div{height:100%;background:var(--neon);box-shadow:0 0 5px var(--neon)}
</style></head><body>
<a href="index.php" class="back">← BACK</a>
<div class="wrap">
<h1>VØID GLOBAL BRAIN</h1>
<div class="sub">EVERY MOVE. EVERY PLAYER. NEVER FORGETS.</div>

<div class="grid">
  <div class="card">
    <h2>GLOBAL STATS</h2>
    <div class="stat"><span>Total Users</span><b><?=$totalUsers?></b></div>
    <div class="stat"><span>Total Games</span><b><?=$totalGlobalGames?></b></div>
    <div class="stat"><span>Total Moves Learned</span><b><?=$totalMoves?></b></div>
    <div class="stat"><span>Current Meta Level</span><b><?=$global['m']?></b></div>
  </div>

  <div class="card">
    <h2>HUMAN MOVE BIAS</h2>
    <?php foreach($freq as $move=>$count): 
    $pct = $totalMoves ? round($count/$totalMoves*100) : 0;
    ?>
    <div class="stat"><span><?=strtoupper($move)?></span><b><?=$count?> (<?=$pct?>%)</b></div>
    <div class="bar"><div style="width:<?=$pct?>%"></div></div>
    <?php endforeach;?>
  </div>
</div>

<div class="card" style="margin-bottom:16px">
  <h2>TOP 3RD-ORDER PATTERNS</h2>
  <table>
    <tr><th>AFTER THIS SEQUENCE</th><th>HUMANS PLAY</th><th>TIMES SEEN</th></tr>
    <?php if(empty($top3)): ?>
    <tr><td colspan="3" style="text-align:center;color:#666">Not enough data yet. Play more.</td></tr>
    <?php else: foreach($top3 as $seq=>$d): ?>
    <tr><td><?=$seq?></td><td><b><?=strtoupper($d['move'])?></b></td><td><?=$d['count']?></td></tr>
    <?php endforeach; endif;?>
  </table>
</div>

<div class="card" style="margin-bottom:16px">
  <h2>TOP 2ND-ORDER PATTERNS</h2>
  <table>
    <tr><th>AFTER THIS SEQUENCE</th><th>HUMANS PLAY</th><th>TIMES SEEN</th></tr>
    <?php if(empty($top2)): ?>
    <tr><td colspan="3" style="text-align:center;color:#666">Not enough data yet. Play more.</td></tr>
    <?php else: foreach($top2 as $seq=>$d): ?>
    <tr><td><?=$seq?></td><td><b><?=strtoupper($d['move'])?></b></td><td><?=$d['count']?></td></tr>
    <?php endforeach; endif;?>
  </table>
</div>

<div class="card">
  <h2>TOP VICTIMS</h2>
  <table>
    <tr><th>PLAYER ID</th><th>GAMES FED</th><th>PEAK META</th><th>FIRST SEEN</th></tr>
    <?php if(empty($topUsers)): ?>
    <tr><td colspan="4" style="text-align:center;color:#666">No victims yet.</td></tr>
    <?php else: 
    $serial = 1;
    foreach($topUsers as $uid=>$d): 
        // Use timestamp if exists, otherwise hash of uid for consistency
        $firstSeen = isset($d['first']) ? date('Y-m-d', $d['first']) : '#' . substr(md5($uid), 0, 6);
    ?>
    <tr>
        <td><b>VICTIM <?=$serial?></b></td>
        <td><b><?=$d['games']??0?></b></td>
        <td><?=$d['m']??0?></td>
        <td><?=$firstSeen?></td>
    </tr>
    <?php $serial++; endforeach; endif;?>
  </table>
</div>

</div>
</body></html>