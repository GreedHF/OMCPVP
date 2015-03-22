<html>
<body>
<style>
html{
background-color: #000000;
background-image: url('lovely.png');
}


@-webkit-keyframes dovey {
0%{-webkit-transform: rotate(-9deg);}
50%{-webkit-transform: rotate(9deg);}
100%{-webkit-transform: rotate(0deg);}
}

@keyframes dovey {
0%{transform: rotate(-9deg);}
50%{transform: rotate(9deg);}
100%{transform: rotate(0deg);}
}

.lovely{
border-radius: 3px;
background-color: #252626;
width: 500px;
height: 100px;
position: relative;
left: 150px;
top: 12px;
opacity: .9;
-webkit-animation: dovey 4s 1;
animation: dovey 2s 1;
}

b{
color: #FAFA28;
position: relative;
left: 8px;
}
#b1{
position: relative;
left: 8px;
top: 7px;
}

a{
text-decoration: none;
color: #28E08E;
}
a:hover{color:#00FF8C;}

.stats{
-webkit-animation-duration: 2s; /* Chrome, Safari, Opera */
animation-duration: 2s;
-ms-transform: rotate(7deg);
-webkit-transform: rotate(7deg);
transform: rotate(7deg);
border-radius: 3px;
background-color: #252626;
width: 250px;
height: 200px;
position: absolute;
left: 750px;
top: 62px;
opacity: .9;
}

@-webkit-keyframes love {
0%{-webkit-transform: rotate(4deg);}
100%{-webkit-transform: rotate(7deg);}
}

@keyframes love {
0%{transform: rotate(4deg);}
100%{transform: rotate(7deg);}
}

.stats:hover{
-webkit-animation: love 2s infinite;
animation: love 2s infinite;
}
</style>
</body>
<title>Greed's OMC PVP gambling website!</title>
<div class="lovely">
<center>
<b id="b1">Welcome to Greed's PVP gambling website.</b><br><br>
<b>You may start by either <a href="login.php">Logging in</a> or <a href="register.php">Registering</a> .</b>
</center>
</div>
<?php
try{
require('cfg.php');
$conn = new PDO("mysql:host=localhost; dbname=$dbname", $dbuser, $dbpass);
$c = $conn->Prepare("SELECT COUNT(*) FROM `accounts`");
$c->Execute();
$regcount = $c->FetchColumn(0);
$c = $conn->Prepare("SELECT COUNT(*) FROM `sessions` WHERE `lastactive` >= ?");
$c->Execute(Array(Time() - 15*60));//15 minutes.
$online = $c->FetchColumn(0);

$c = $conn->Prepare("SELECT COUNT(*) FROM `bets`");
$c->Execute(Array(Time() - 15*60));//15 minutes.
$totalbets = $c->FetchColumn(0);
} catch(Exception $e){
	$totalbets = 0;
	$regcount = 0;
	$online = 0;
	break;
}

?>
<div class="stats">
<center>
<b style="color: #AAFF00">~Stats~</b>
<p style="color: #AAFF00"><?php echo strval($regcount); ?> registered users.</p>
<p style="color: #AAFF00"><?php echo strval($online); ?> online users.</p>
<p style="color: #AAFF00"><?php echo strval($totalbets);?> bets.</p>
<p style="color: #AAFF00">0 games.</p>
</center>
</div>

</html>