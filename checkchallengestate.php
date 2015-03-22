<?php
if (!isset($_COOKIE['cookie']) || !isset($_POST['id'])) Exit;
$cookie = $_COOKIE['cookie'];
try{
require('cfg.php');
$conn = new PDO("mysql:host=localhost; dbname=$dbname", $dbuser, $dbpass);
$c = $conn->Prepare("SELECT `csrf`, `ip`, `username` FROM `sessions` WHERE `cookie` = ?");
$c->Execute(Array($cookie));
$r = $c->Fetch(PDO::FETCH_ASSOC);
if ($r == null) Exit;
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
if ($privileges == 0) Die('false|');
$c = $conn->Prepare("SELECT * FROM `challenges` WHERE `id` = ?");
$c->Execute(Array($_POST['id']));
$r = $c->Fetch(PDO::FETCH_ASSOC);
if ($r == null) die('false|');
//if ($r['challenger'] != $username && $r['challenged'] != $username) die('false|');

if ($r['accepted'] == 2){
	if ($r['time'] + 60 < Time())
	{
		$c = $conn->Prepare("UPDATE `challenges` SET `accepted` = 0 WHERE `id` = ?");
		$c->Execute(Array($_POST['id']));
		Die('true|timeout|');
	}
	Exit();
}
if ($r['accepted'] == 0) Die('true|rejected|');

$c = $conn->Prepare("SELECT * FROM `bets` WHERE `id` = ?");
$c->Execute(Array($_POST['id']));
$r = $c->Fetch(PDO::FETCH_ASSOC);
if (!$r) Die('false|');
if ($r['result'] == 1) //we won.
	die('true|true|' . $r['amount'] . '|' . $r['roll']);
else
	die('true|false|' . $r['amount'] . '|' . $r['roll']);

}catch (Exception $e){
Die('false|');
}
?>