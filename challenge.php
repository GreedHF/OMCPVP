<?php
if (!isset($_COOKIE['cookie'])) Exit();
if (!isset($_POST['un']) || !isset($_POST['amount']) || !isset($_POST['csrf'])) Exit();
$cookie = $_COOKIE['cookie'];
try{
$amt = (float)$_POST['amount'];
require('cfg.php');
$conn = new PDO("mysql:host=localhost; dbname=$dbname", $dbuser, $dbpass);
$c = $conn->Prepare("SELECT `csrf`, `ip`, `username` FROM `sessions` WHERE `cookie` = ?");
$c->Execute(Array($cookie));
$r = $c->Fetch(PDO::FETCH_ASSOC);
if ($r == null) Exit();
$username = $r['username'];
$csrftoken = $r['csrf'];
if ($csrftoken != $_POST['csrf']) Die('0|false');
if ($_POST['un'] == $username) Exit();

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

$c = $conn->Prepare("SELECT `balance`, `username` FROM `balances` WHERE `username` = ? OR `username` = ?");
$c->Execute(Array($username, $_POST['un']));
$r = $c->FetchAll();
if (count($r) == 1) Die('false|0');
$mybalance = 0;
$oppbalance = 0;
if ($r[0]["username"] == $username)
{
	$mybalance = $r[0]["balance"];
	$oppbalance = $r[1]["balance"];
}
else
{
	$mybalance = $r[1]["balance"];
	$oppbalance = $r[0]["balance"];
}
if ($mybalance < $amt || $oppbalance < $amt) Die('false|0');

$c = $conn->Prepare("INSERT INTO `challenges` (`challenger`, `challenged`, `amount`, `time`) VALUES (?, ?, ?, ?)");
$c->Execute(Array($username, htmlspecialchars_decode($_POST['un']), $amt, Time()));
$id = $conn->lastInsertId();
Die("true|$id");
}catch (Exception $e){
Die('false|0');
}

?>