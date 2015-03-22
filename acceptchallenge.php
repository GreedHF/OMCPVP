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
if ($csrftoken != $_POST['csrf']) Die('false|');
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
$challenger = $r['challenger'];
$amt = floatval($r['amount']);
if ($amt < 0.0) Die('false|');

$c = $conn->Prepare("SELECT `balance`, `username` FROM `balances` WHERE `username` = ? OR `username` = ?");
$c->Execute(Array($username, $challenger));
$r = $c->FetchAll();
if (count($r) == 1) Die('false|');
$mybalance = 0;
$oppbalance = 0;
if ($r[0]["username"] == $username)
{
	$mybalance = floatval($r[0]["balance"]);
	$oppbalance = floatval($r[1]["balance"]);
}
else
{
	$mybalance = floatval($r[1]["balance"]);
	$oppbalance = floatval($r[0]["balance"]);
}
if ($mybalance < $amt || $oppbalance < $amt) Die('false|');


//if ($r['challenger'] != $username && $r['challenged'] != $username) die('false|');
require_once('cryptoLib.php'); /*http://stackoverflow.com/a/23622282 or use that.*/
$roll = CryptoLib::randomInt(0, 9999);
if ($roll > 4999.5)
	$winner = 0;
else
	$winner = 1;

$c = $conn->Prepare("UPDATE `balances` SET `balance` = `balance` - ? WHERE `username` = ?");
$c->Execute(Array($amt, ($winner == 0) ? $challenger : $username));

$c = $conn->Prepare("UPDATE `balances` SET `balance` = `balance` + ? WHERE `username` = ?");
$c->Execute(Array($amt, ($winner == 0) ? $username : $challenger));

$c = $conn->Prepare("INSERT INTO `bets` (`id`, `amount`, `result`, `challenger`, `challenged`, `roll`) VALUES (?, ?, ?, ?, ?, ?)");
$c->Execute(Array($_POST['id'], $amt, $winner, $challenger, $username, $roll));

$c = $conn->Prepare("UPDATE `challenges` SET `accepted` = 1 WHERE `id` = ?");
$c->Execute(Array($_POST['id']));

die( "true|" . ($winner == 0 ? 'true|' : 'false|') . $amt . '|' . $roll);
}catch (Exception $e){
Die('false|');
}
?>