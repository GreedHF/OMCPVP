<?php
if (!isset($_COOKIE['cookie'])) Exit();
if (!isset($_POST['tick'])) Exit();
$cookie = $_COOKIE['cookie'];
try{
require('cfg.php');
$conn = new PDO("mysql:host=localhost; dbname=$dbname", $dbuser, $dbpass);
$c = $conn->Prepare("SELECT `csrf`, `ip`, `username` FROM `sessions` WHERE `cookie` = ?");
$c->Execute(Array($cookie));
$r = $c->Fetch(PDO::FETCH_ASSOC);
if ($r == null) Exit();
$username = $r['username'];
$csrftoken = $r['csrf'];
//if ($csrftoken != $_POST['csrf']) Die('0|false');
//if ($_POST['un'] == $username) Exit();

if ($r['ip'] != ip2long($_SERVER['REMOTE_ADDR'])){
	$c = $conn->Prepare("DELETE FROM `sessions` WHERE `cookie` = ?");
	$c->Execute(Array($cookie));
	$c = $conn->Prepare("UPDATE `accounts` SET `sessions` = `sessions` - 1 WHERE `username` = ?");
	$c->Execute(Array($r['username']));
	Exit();
}
$c = $conn->Prepare("UPDATE `sessions` SET `lastactive` = ? WHERE `cookie` = ?");
$c->Execute(Array(Time(), $cookie));

$c = $conn->Prepare("SELECT `privileges` FROM `accounts` WHERE `username` = ?");
$c->Execute(Array($username));
$r = $c->Fetch(PDO::FETCH_ASSOC);
if ($r != null){
	$privileges = $r['privileges'];
}
else
	$privileges = 1;
if ($privileges == 0) Die('');
echo Time() . '|';
$c = $conn->Prepare("SELECT * FROM `chat` WHERE `date` > ? ORDER BY `date` DESC LIMIT 0,24");
$c->Execute(Array($_POST['tick']));
$r = $c->FetchAll();
$lastuser = "";
$left = false;
foreach ($r as $msg){
	if ($lastuser != $msg['user'])
	{
		$left = !$left;
		$lastuser = $msg['user'];
	}
	if ($left){
		echo urlencode("<div class=\"replyleftcontainer\">" .
		"<div class=\"replyleftname\">" . htmlspecialchars($msg['user']) . "</div>" .
'<div class="replyleftreply">' . htmlspecialchars($msg['msg']) . '</div></div>') . '|';
	}
	else{
		echo urlencode("<div class=\"replyrightcontainer\">" .
		"<div class=\"replyrightname\">" . htmlspecialchars($msg['user']) . "</div>" .
'<div class="replyrightreply">' . htmlspecialchars($msg['msg']) . '</div></div>') . '|';
	}
}

}catch (Exception $e){
Die(Time());
}
?>