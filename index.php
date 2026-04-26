<?php
$user = $_GET['u'] ?? '';
$user = preg_replace('/[^a-zA-Z0-9]/', '', $user);
?>
<!DOCTYPE html><html><head><meta name="viewport" content="width=device-width,1,maximum-scale=1">
<title>RPS ARENA</title><style>
:root{--neon:#ff073a;--cyan:#00ffff;--green:#00ff88;--yellow:#ffaa00;--purple:#a020f0;--glow:0 0 5px var(--neon),0 0 15px var(--neon)}
@keyframes neonmove{0%{box-shadow:-2px 0 10px var(--neon)}25%{box-shadow:0 -2px 10px var(--neon)}50%{box-shadow:2px 0 10px var(--neon)}75%{box-shadow:0 2px 10px var(--neon)}100%{box-shadow:-2px 0 10px var(--neon)}}
@keyframes float{0%{transform:translate(0,0)}50%{transform:translate(20px,-30px)}100%{transform:translate(0,0)}}
@keyframes glitch{0%{text-shadow:2px 0 #ff073a,-2px 0 #00ffff}25%{text-shadow:-2px 0 #ff073a,2px 0 #00ffff}50%{text-shadow:2px 0 #ff073a,-2px 0 #00ffff}100%{text-shadow:-2px 0 #ff073a,2px 0 #00ffff}}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:0.6}}
@keyframes fadeIn{0%{opacity:0}100%{opacity:1}}
@keyframes shake{0%,100%{transform:translateX(0)}10%,30%,50%,70%,90%{transform:translateX(-5px)}20%,40%,60%,80%{transform:translateX(5px)}}
*{box-sizing:border-box;margin:0}
body{background:#000;color:#e9edef;font-family:system-ui;display:flex;justify-content:center;align-items:center;min-height:100vh;padding:20px}
.bg{position:fixed;inset:0;z-index:0;pointer-events:none}
.bubble-bg{position:absolute;border-radius:50%;background:radial-gradient(circle, rgba(255,7,58,0.15) 0%, transparent 70%);box-shadow:0 0 50px rgba(255,7,58,0.25);animation:float 8s ease-in-out infinite}
.b1{width:220px;height:220px;top:10%;left:-70px}
.b2{width:160px;height:160px;bottom:15%;right:-50px;animation-delay:2s}
.wrap{width:100%;max-width:480px;z-index:1}
.card{background:#111;border:2px solid var(--neon);border-radius:16px;padding:28px;text-align:center;animation:neonmove 3s linear infinite}
h1{font-size:24px;margin-bottom:8px;text-shadow:var(--glow);letter-spacing:3px}
.sub{font-size:11px;color:#ff5c7a;margin-bottom:20px;letter-spacing:2px}
.userbox{margin-bottom:16px}
.userbox input{width:100%;padding:12px;background:#0a0a0a;border:2px solid var(--neon);border-radius:10px;color:#e9edef;text-align:center;font-size:14px;font-weight:700;letter-spacing:1px}
.userbox input:focus{outline:none;box-shadow:var(--glow)}
.userbox label{font-size:10px;color:#ff5c7a;display:block;margin-bottom:6px;letter-spacing:1px}
.db-link{display:block;width:100%;padding:10px;margin-bottom:12px;background:#000;border:2px solid var(--purple);border-radius:10px;color:var(--purple);text-decoration:none;font-weight:700;font-size:12px;letter-spacing:2px;text-shadow:0 0 8px var(--purple);transition:.2s}
.db-link:hover{background:var(--purple);color:#000;box-shadow:0 0 15px var(--purple)}
.db-link:active{transform:scale(0.97)}
.guide-link{display:block;width:100%;padding:10px;margin-bottom:16px;background:#000;border:2px solid var(--cyan);border-radius:10px;color:var(--cyan);text-decoration:none;font-weight:700;font-size:12px;letter-spacing:2px;text-shadow:0 0 8px var(--cyan);transition:.2s}
.guide-link:hover{background:var(--cyan);color:#000;box-shadow:0 0 15px var(--cyan)}
.guide-link:active{transform:scale(0.97)}
.modes{display:flex;flex-direction:column;gap:12px}
.mode{display:block;padding:16px;border:2px solid;border-radius:12px;text-decoration:none;color:#e9edef;transition:.2s;position:relative;overflow:hidden}
.mode:active{transform:scale(0.97)}
.mode h2{font-size:18px;margin-bottom:4px;letter-spacing:2px}
.mode p{font-size:11px;opacity:0.8;letter-spacing:1px}
.neon{border-color:var(--green);background:linear-gradient(135deg,#001a0d,0%,#111 100%)}
.neon h2{color:var(--green);text-shadow:0 0 10px var(--green)}
.ai{border-color:var(--cyan);background:linear-gradient(135deg,#001a1a,0%,#111 100%)}
.ai h2{color:var(--cyan);text-shadow:0 0 10px var(--cyan)}
.nightmare{border-color:var(--yellow);background:linear-gradient(135deg,#1a1300,0%,#111 100%)}
.nightmare h2{color:var(--yellow);text-shadow:0 0 10px var(--yellow)}
.devil{border-color:var(--neon);background:linear-gradient(135deg,#1a0006,0%,#111 100%)}
.devil h2{color:var(--neon);text-shadow:var(--glow)}
.void{border-color:#fff;background:linear-gradient(135deg,#000,0%,#111 100%);animation:neonmove 2s linear infinite}
.void h2{color:#fff;text-shadow:var(--glow);animation:glitch 3s infinite}
.void::before{content:"";position:absolute;inset:0;background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(255,7,58,0.03) 2px,rgba(255,7,58,0.03) 4px);pointer-events:none;animation:pulse 4s infinite}
.warning{font-size:9px;color:#666;margin-top:20px;line-height:1.5}
.warning b{color:#ff073a}
.popup{position:fixed;inset:0;background:rgba(0,0,0,0.98);z-index:999;display:none;justify-content:center;align-items:center;animation:fadeIn.3s}
.popup.show{display:flex}
.popupBox{background:#111;border:2px solid #fff;border-radius:16px;padding:24px;max-width:400px;text-align:center;animation:neonmove 2s linear infinite,shake.5s}
.popupBox h2{color:#ff073a;font-size:20px;margin-bottom:14px;text-shadow:var(--glow);animation:glitch 2s infinite;letter-spacing:2px}
.popupBox p{font-size:12px;line-height:1.7;margin-bottom:18px;color:#e9edef;text-align:left}
.popupBox p b{color:#ff073a}
.popupBox .disclaimer{font-size:10px;color:#666;margin-top:12px;font-style:italic}
.popupBtns{display:flex;gap:10px}
.popupBtns button{flex:1;padding:12px;border-radius:8px;font-weight:700;cursor:pointer;font-size:14px;border:2px solid;transition:.2s}
.popupBtns .cancel{background:transparent;border-color:#666;color:#666}
.popupBtns .cancel:hover{background:#666;color:#000}
.popupBtns .confirm{background:#ff073a;border-color:#ff073a;color:#000;box-shadow:0 0 10px #ff073a}
.popupBtns .confirm:hover{transform:scale(1.05)}
.footer{font-size:10px;color:#666;text-align:center;margin-top:20px;padding-top:16px;border-top:1px solid #222;letter-spacing:1px;line-height:1.5}
.footer b{color:#888}
</style>
<script>
function go(game){
  let u = document.getElementById('user').value.trim();
  let url = game;
  if(u) url += '?u=' + encodeURIComponent(u);
  
  if(game === 'rpsvoid.php'){
    showVoidWarning(url);
  }else{
    window.location = url;
  }
}

function goStats(){
  let u = document.getElementById('user').value.trim();
  let url = 'stats.php';
  if(u) url += '?u=' + encodeURIComponent(u);
  window.location = url;
}

function goGuide(){
  let u = document.getElementById('user').value.trim();
  let url = 'guide.php';
  if(u) url += '?u=' + encodeURIComponent(u);
  window.location = url;
}

function showVoidWarning(url){
  document.getElementById('voidPopup').classList.add('show');
  document.getElementById('voidConfirm').onclick = function(){
    window.location = url;
  };
}

function closePopup(){
  document.getElementById('voidPopup').classList.remove('show');
}
</script>
</head><body>
<div class="popup" id="voidPopup">
<div class="popupBox">
<h2>VØID WARNING</h2>
<p>
This AI is designed to <b>psychologically dominate</b> you. It will:<br><br>
• Track your IP, loss streaks, and behavioral patterns<br>
• Remember every failure forever - resets mean nothing<br>
• Mock you with increasing hostility as you lose<br>
• Display your data back to you as intimidation<br><br>
<b>It will humiliate you. It will remember your shame. It will make you question your intelligence.</b><br><br>
The VØID is relentless. It adapts. It learns. It does not forgive.<br><br>
<span class="disclaimer">This is still just a game. No real data is shared. But your ego might not survive.</span>
</p>
<div class="popupBtns">
<button class="cancel" onclick="closePopup()">I'M SCARED</button>
<button class="confirm" id="voidConfirm">ENTER THE VØID</button>
</div>
</div>
</div>

<div class="bg"><div class="bubble-bg b1"></div><div class="bubble-bg b2"></div></div>
<div class="wrap"><div class="card">
<h1>RPS ARENA</h1>
<div class="sub">SELECT YOUR OPPONENT</div>

<div class="userbox">
<label>ENTER USERNAME (OPTIONAL)</label>
<input type="text" id="user" placeholder="yourname" value="<?=$user?>" maxlength="16">
</div>

<a class="db-link" href="javascript:goStats()">AI DATABASE</a>
<a class="guide-link" href="javascript:goGuide()">RPS GUIDE</a>

<div class="modes">
  <a class="mode neon" href="javascript:go('rps.php')">
    <h2>RPS NEON</h2>
    <p>EASY • BASIC PATTERN TRACKING</p>
  </a>
  
  <a class="mode ai" href="javascript:go('rpsai.php')">
    <h2>RPS AI</h2>
    <p>NORMAL • 2ND-ORDER MARKOV</p>
  </a>
  
  <a class="mode nightmare" href="javascript:go('rpsN.php')">
    <h2>RPS NIGHTMARE</h2>
    <p>HARD • 3RD-ORDER + IOCAINE</p>
  </a>
  
  <a class="mode devil" href="javascript:go('rpsdev.php')">
    <h2>RPS DEVIL</h2>
    <p>EXTREME • GLOBAL BRAIN + PROFILING</p>
  </a>
  
  <a class="mode void" href="javascript:go('rpsvoid.php')">
    <h2>RPS VØID</h2>
    <p>IMPOSSIBLE • TIER 14 + PSYCHOLOGICAL WARFARE</p>
  </a>
</div>

<div class="warning">
<b>WARNING:</b> VØID mode tracks IP, loss streaks, and psychological state.<br>
Your data is never forgotten. Reset does not save you.
</div>

<div class="footer">
<b>stoicartist™</b> © 2026 all rights reserved<br>
credit: source code provided by meta ai
</div>

</div></div>
</body></html>