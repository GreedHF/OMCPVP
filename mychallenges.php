<?php
if (!isset($_COOKIE['cookie'])) Exit();
if (!isset($_POST['lastupdate'])) Exit();
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
if ($privileges == 0) Die('2');
$t = time();
$c = $conn->Prepare("SELECT * FROM `challenges` WHERE `challenged` = ? AND `time` > ? AND `accepted` = 2");
$c->Execute(Array($username, $_POST['lastupdate']));
$r = $c->FetchAll();
if (count($r) == 0) Exit;
echo '1' . '|' . $t . '|';
foreach ($r as $challenge){
	echo urlencode("ichallenged(" . $challenge['id'] . ',"' .
	htmlspecialchars($challenge['challenger']) . '",' .
	$challenge['amount'] . ');') . '|';
}

}catch (Exception $e){
Die('0');
}

?>