<?php
if (!isset($_COOKIE['cookie'])) Exit();
if (!isset($_POST['msg']) || !isset($_POST['csrf'])) Exit();
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
if ($csrftoken != $_POST['csrf']) Die('0|false');
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
if ($privileges == 0) Die('false|0');
$c = $conn->Prepare("INSERT INTO `chat` (`user`, `msg`, `date`) VALUES (?, ?, ?)");
$c->Execute(array($username, $_POST['msg'], Time()));
}catch (Exception $e){
die('');
}
?>