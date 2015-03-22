<?php
if (!isset($_COOKIE['cookie'])) Exit;
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
if ($privileges == 0) Die('Error: You have been banned.');
}catch (Exception $e){
Die('E:Exception => unable to continue, probably too many players, try refreshing in a little bit.');
}

$c = $conn->Prepare("SELECT * FROM `sessions` ORDER BY `username` ASC LIMIT 0,23");
$c->Execute();
$r = $c->FetchAll();
foreach ($r as $session){
	if ($session['lastactive'] < Time() - 2*60) continue; //hasn't been active for 15 minutes so ignore it.
	$c = $conn->prepare("SELECT `balance` FROM `balances` WHERE `username` = ?");
	$c->Execute(Array($session['username']));
	$r = $c->Fetch(PDO::FETCH_ASSOC);
	if (!$r) continue;
	if ($r['balance'] == 0) continue;
	$id = htmlspecialchars($session['username']);
	echo "<p class=\"user\" id=\"$id\" onclick=challenge(this)>";
	echo "<the class=\"useramt\">";
	echo $r['balance'];
	echo "</the>";
	echo htmlspecialchars($session['username']);
	echo "</p>";
}
?>