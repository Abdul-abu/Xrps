<?php
$user = $_GET['u'] ?? '';
$user = preg_replace('/[^a-zA-Z0-9]/', '', $user);
?>
<!DOCTYPE html><html><head><meta name="viewport" content="width=device-width,1,maximum-scale=1">
<title>VØID DOCTRINE - RPS KINGMODE</title><style>
:root{--neon:#ff073a;--cyan:#00ffff;--green:#00ff88;--yellow:#ffaa00;--purple:#a020f0;--glow:0 0 5px var(--neon),0 0 15px var(--neon)}
@keyframes neonmove{0%{box-shadow:-2px 0 10px var(--neon)}25%{box-shadow:0 -2px 10px var(--neon)}50%{box-shadow:2px 0 10px var(--neon)}75%{box-shadow:0 2px 10px var(--neon)}100%{box-shadow:-2px 0 10px var(--neon)}}
@keyframes glitch{0%{text-shadow:2px 0 #ff073a,-2px 0 #00ffff}25%{text-shadow:-2px 0 #ff073a,2px 0 #00ffff}50%{text-shadow:2px 0 #ff073a,-2px 0 #00ffff}100%{text-shadow:-2px 0 #ff073a,2px 0 #00ffff}}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:0.7}}
*{box-sizing:border-box;margin:0}
body{background:#000;color:#e9edef;font-family:system-ui;padding:20px;line-height:1.6}
.back{position:fixed;top:12px;left:12px;padding:6px 12px;background:#000;border:2px solid var(--neon);border-radius:8px;color:var(--neon);font-weight:700;font-size:11px;text-decoration:none;z-index:999}
.back:hover{background:var(--neon);color:#000;box-shadow:var(--glow)}
.wrap{max-width:800px;margin:40px auto}
h1{font-size:28px;text-shadow:var(--glow);text-align:center;margin-bottom:8px;letter-spacing:3px;animation:glitch 3s infinite}
.sub{font-size:11px;color:#ff5c7a;text-align:center;margin-bottom:30px;letter-spacing:2px}
h2{font-size:18px;color:var(--neon);margin:30px 0 12px;text-shadow:0 0 8px var(--neon);letter-spacing:2px;border-bottom:1px solid #222;padding-bottom:6px}
h3{font-size:14px;color:var(--cyan);margin:20px 0 8px;text-shadow:0 0 5px var(--cyan)}
p{font-size:13px;margin-bottom:12px;color:#ccc}
.card{background:#111;border:2px solid var(--neon);border-radius:12px;padding:18px;margin:16px 0;animation:neonmove 4s linear infinite}
.card.cyan{border-color:var(--cyan)}
.card.green{border-color:var(--green)}
.card.yellow{border-color:var(--yellow)}
.card.purple{border-color:var(--purple)}
.card h3{margin-top:0}
b{color:var(--neon)}
code{background:#000;padding:2px 6px;border-radius:4px;color:var(--green);font-size:12px;border:1px solid #222}
.example{background:#0a0a0a;border-left:3px solid var(--yellow);padding:10px;margin:12px 0;font-size:12px;border-radius:0 6px 6px 0}
.example b{color:var(--yellow)}
ul{margin:10px 0 10px 20px;font-size:13px}
li{margin:6px 0;color:#bbb}
.level{display:flex;align-items:start;gap:12px;margin:14px 0;padding:12px;background:#0a0a0a;border-radius:8px;border:1px solid #222}
.level-num{min-width:70px;font-weight:700;color:var(--neon);font-size:12px;text-shadow:0 0 5px var(--neon)}
.level-desc{font-size:12px;color:#ccc}
.warning{background:rgba(255,7,58,0.1);border:2px solid var(--neon);padding:14px;border-radius:8px;margin:20px 0;font-size:12px;animation:pulse 3s infinite}
.warning b{color:var(--neon)}
table{width:100%;border-collapse:collapse;font-size:12px;margin:12px 0}
th,td{padding:8px;text-align:left;border-bottom:1px solid #222}
th{color:var(--cyan);font-size:11px}
td b{color:var(--neon)}
.footer{font-size:10px;color:#666;text-align:center;margin-top:50px;padding-top:16px;border-top:1px solid #222;letter-spacing:1px;line-height:1.5}
.footer b{color:#888}
</style></head><body>
<a href="index.php<?=$user?'?u='.$user:''?>" class="back">← BACK</a>
<div class="wrap">
<h1>VØID DOCTRINE</h1>
<div class="sub">ADVANCED RPS • IOCAINE WARFARE • KINGMODE</div>

<div class="warning">
<b>NOTE:</b> This guide teaches you to think like the VØID. Normal humans play Level 0-1. After reading this, you'll play Level 3+. You are responsible for the egos you crush.
</div>

<h2>PART 1: WHY YOU LOSE</h2>
<div class="card">
<p>Normal RPS is not 33/33/33. Humans are predictable garbage. We tilt, we pattern, we copy. The VØID exploits this with 3 weapons:</p>
<ul>
<li><b>Markov Chains:</b> Remembers your last 2-3 moves and predicts the 4th</li>
<li><b>Iocaine Powder:</b> Models what level you're thinking at</li>
<li><b>Global Brain:</b> Uses data from every player who ever lost to learn human bias</li>
</ul>
<p>If you play "random", you lose. If you play "smart", you lose slower. To win, you must play <b>meta</b>.</p>
</div>

<h2>PART 2: IOCAINE POWDER - THE LEVELS</h2>
<div class="card cyan">
<p>Iocaine Powder is the mind game. Each level is "I know that you know that I know...". Most players stop at Level 1. VØID starts at Level 2. Kingmode starts at Level 4.</p>

<div class="level">
  <div class="level-num">LEVEL 0<br>MONKEY</div>
  <div class="level-desc">You play random. Or you always play rock. You are free data. VØID farms you for global brain. Winrate vs VØID: <b>25%</b></div>
</div>

<div class="level">
  <div class="level-num">LEVEL 1<br>COUNTER</div>
  <div class="level-desc"><b>Logic:</b> "He played rock last time, so he'll play rock again. I play paper."<br><b>Beats:</b> Level 0<br><b>Loses to:</b> VØID default. Winrate: <b>35%</b></div>
</div>

<div class="level">
  <div class="level-num">LEVEL 2<br>COUNTER-COUNTER</div>
  <div class="level-desc"><b>Logic:</b> "He knows I think he'll play rock, so he'll play scissors to beat my paper. I play rock."<br><b>Beats:</b> Level 1, VØID when meta=0<br><b>Loses to:</b> VØID when meta>2. Winrate: <b>48%</b></div>
</div>

<div class="level">
  <div class="level-num">LEVEL 3<br>TRIPLE THINK</div>
  <div class="level-desc"><b>Logic:</b> "He thinks I think he thinks I'll play rock, so he'll play paper. I play scissors."<br><b>Beats:</b> Level 2, VØID when meta<6<br><b>This is where you start winning.</b> Winrate: <b>55%</b></div>
</div>

<div class="level">
  <div class="level-num">LEVEL 4<br>EXPECTIMAX</div>
  <div class="level-desc"><b>Logic:</b> "There's 33% chance he's Level 0, 33% he's Level 1, 33% he's Level 2. I calculate EV for rock/paper/scissors vs all 3 and pick highest."<br><b>Beats:</b> Everything below. This is what VØID does at meta 6+.<br><b>Winrate:</b> <b>65-70%</b> if you do it right.</div>
</div>

<div class="level">
  <div class="level-num">LEVEL 5+<br>KINGMODE</div>
  <div class="level-desc"><b>Logic:</b> You track opponent's Iocaine level in real time. If they counter you twice, they're Level 2. You jump to Level 3. If they jump too, you go Level 4. You stay 1 level above them always.<br><b>Also:</b> You inject noise. Play Level 0 for 3 rounds to bait them down, then spike to Level 4.<br><b>Winrate vs VØID:</b> <b>70-85%</b>. You are the nightmare now.</div>
</div>

<div class="example">
<b>Example Live Game:</b><br>
Round 1: You play rock, VØID plays paper. You lose. VØID meta=2.<br>
Round 2: You think "it expects me to play scissors vs its paper, so it'll play rock. I play paper." VØID plays rock. You win. Meta=3.<br>
Round 3: You think "now it knows I did Level 2, so it'll do Level 3 and play scissors. I play rock." VØID plays scissors. You win again. Meta=4.<br>
<b>You are now playing 4D chess. VØID is sweating.</b>
</div>
</div>

<h2>PART 3: BEATING MARKOV CHAINS</h2>
<div class="card green">
<h3>How VØID Predicts You</h3>
<p>VØID stores your last 3 moves. If you played <code>rock|paper|rock</code>, it checks how often you played rock/paper/scissors next. If you played scissors 8/10 times after that sequence, it plays rock to beat you.</p>

<h3>How to Beat It</h3>
<ul>
<li><b>Track 4 moves:</b> VØID only does 3rd-order. If you remember 4 moves, you're deeper than it.</li>
<li><b>Break your own patterns:</b> After <code>rock|paper|rock</code>, force yourself to play paper even if you want scissors.</li>
<li><b>Poison the chain:</b> Play Level 0 for 10 rounds to feed it bad data, then switch to Level 4.</li>
<li><b>Use ABAB pattern:</b> <code>rock|paper|rock|paper</code>. VØID has anti-spam logic and will flip its prediction. Counter the flip.</li>
</ul>

<div class="example">
<b>Poisoning Example:</b><br>
Games 1-10: Spam rock every time. VØID learns "this guy always plays rock".<br>
Game 11: You play paper. VØID plays paper expecting rock. Draw.<br>
Game 12: You play scissors. VØID plays paper. You win.<br>
<b>You just lied to the database. VØID trusted you. It was wrong.</b>
</div>
</div>

<h2>PART 4: KINGMODE PROTOCOL</h2>
<div class="card yellow">
<p>Kingmode = Iocaine Level 4+ combined with Markov breaking. Checklist:</p>

<table>
<tr><th>Rule</th><th>Why</th></tr>
<tr><td><b>1. Never play Level 0</b></td><td>Random = 33% winrate. Unacceptable.</td></tr>
<tr><td><b>2. Track 4+ moves</b></td><td>Breaks VØID's 3rd-order Markov</td></tr>
<tr><td><b>3. Count opponent meta</b></td><td>If VØID meta jumps +2 after you win, it learned. Jump your level too.</td></tr>
<tr><td><b>4. Inject 20% chaos</b></td><td>Every 5th round, play true random. Prevents VØID from 100% modeling you.</td></tr>
<tr><td><b>5. Bait and switch</b></td><td>Act Level 1 for 3 rounds, then spike to Level 4. VØID lags 1 round behind.</td></tr>
<tr><td><b>6. Watch loss streaks</b></td><td>If you lose 3 in a row, VØID meta is higher than you. Reset to Level 0 for 2 rounds to drop its meta, then climb again.</td></tr>
</table>

<h3>The Kingmode Loop</h3>
<p>1. Start Level 2. Win 1-2 rounds.<br>
2. VØID adapts to Level 2, you lose 1.<br>
3. Jump to Level 4. Win 2-3 rounds.<br>
4. VØID adapts to Level 4, you lose 1.<br>
5. Drop to Level 1 to confuse it. Win 1.<br>
6. Spike back to Level 4. Repeat.<br>
<b>Result: 65-75% winrate. You are now the final boss.</b></p>
</div>

<h2>PART 5: ADVANCED TACTICS</h2>
<div class="card purple">
<h3>Tilt Exploitation</h3>
<p>Humans play paper 36% after losing. If VØID beats you, next round play scissors. If you beat VØID, next round it expects you to get cocky and repeat. Don't.</p>

<h3>Timing Tells</h3>
<p>In real RPS, fast throw = Level 0-1, hesitation = Level 2+. Online, if opponent picks instantly, they're predictable. If they delay, they're calculating. Play Level +1 of what you think they are.</p>

<h3>The Meta 246 Counter</h3>
<p>If VØID hits meta 100+, it loops back to Level 0 logic. High meta ≠ smart. It means it overfit. Play Level 1-2 vs meta 200+ and you'll win. The guy who beat meta 246 did this.</p>

<h3>Global Brain Poisoning</h3>
<p>Every game you play trains VØID for everyone. If you want to help humanity, play true random 50 games to dilute the global brain. If you want to be king, play perfect Iocaine 50 games to make VØID unbeatable for noobs, then only you can beat it.</p>
</div>

<div class="warning">
<b>FINAL LAW:</b> There is no true random. There is only levels. If you can think 1 level above your opponent, you win 65%+. If you can track 4 moves while doing Iocaine Level 4, you win 80%+. If you can do both while poisoning the global brain, you ARE the VØID. Go forth and dominate.
</div>

<div class="footer">
<b>stoicartist™</b> © 2026 all rights reserved<br>
credit: guide written by meta ai, source code provided by meta ai
</div>

</div>
</body></html>