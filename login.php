<?php
Header('X-Frame-Options: DENY');


if (isset($_POST['username']) && isset($_POST['phashed'])){
try{
require('cfg.php');
$conn = new PDO("mysql:host=localhost; dbname=$dbname", $dbuser, $dbpass);

$tmp = Array();
$tmp[0] = ip2long($_SERVER['REMOTE_ADDR']);
if (isset($_COOKIE['nm'])) $tmp[1] = $_COOKIE['nm'];

if (count($tmp) == 2)
	$c = $conn->Prepare("SELECT `attempts`, `lastlogin` FROM `misery4crackers` WHERE `ip` = ? AND `cookie` = ?");
else
	$c = $conn->Prepare("SELECT `attempts`, `lastlogin` FROM `misery4crackers` WHERE `ip` = ?");
$c->Execute($tmp);
$r = $c->Fetch(PDO::FETCH_ASSOC);
if ($r){
	if ($r['attempts'] >= 4 && $r['lastlogin'] + 30 * 60 > Time() ) die('4');
	if ($r['attempts'] >= 4)
		$r['attempts'] = 0;
	if (count($tmp) == 2)
	$c = $conn->Prepare("UPDATE `misery4crackers` SET `lastlogin` = ?, `attempts` = ? + 1 WHERE `ip` = ? OR `cookie` = ?");
else
	$c = $conn->Prepare("UPDATE `misery4crackers` SET `lastlogin` = ?, `attempts` = ? WHERE `ip` = ?");
	$c->Execute(array_merge(Array(time(), 1 + $r['attempts']), $tmp));
}
else{
	$c = $conn->Prepare("INSERT INTO `misery4crackers` (`ip`, `cookie`, `lastlogin`, `attempts`) VALUES (?, ?, ?, ?)");
	$c->Execute(Array(ip2long($_SERVER['REMOTE_ADDR']), md5($_SERVER['REMOTE_ADDR']), time(), 1));
}
setcookie('nm', md5($_SERVER['REMOTE_ADDR']), 0, '/');

$c = $conn->Prepare("SELECT `salt` FROM `accounts` WHERE `username` = ?");
$c->Execute(Array($_POST['username']));
$r = $c->Fetch(PDO::FETCH_ASSOC);
if (!$r) die('0');
$salt = $r['salt'];
$pw = hash('sha512', $_POST['phashed'] . $salt, false);
$c = $conn->Prepare("SELECT `privileges` FROM `accounts` WHERE `username` = ? AND `password` = ?");
$c->Execute(Array($_POST['username'], $pw));
$r = $c->Fetch(PDO::FETCH_ASSOC);
if (!$r) die('1');
if ($r['privileges'] == 0) die('2');

$c = $conn->Prepare("UPDATE `accounts` SET `sessions` = `sessions` + 1, `lastlogin` = ? WHERE `username` = ?");
$c->Execute(Array(Time(), $_POST['username']));

$c = $conn->Prepare("INSERT INTO `sessions` (`ip`, `lastactive`, `username`, `csrf`, `useragent`, `cookie`) VALUES (?, ?, ?, ?, ?, ?)");
$csrf = md5(openssl_random_pseudo_bytes(30));
$cookie = md5(openssl_random_pseudo_bytes(30));
$c->Execute(Array(ip2long($_SERVER['REMOTE_ADDR']), Time(), $_POST['username'], $csrf, $_SERVER['HTTP_USER_AGENT'], $cookie));
setcookie('cookie', $cookie, 0, '/');
die('3');
}catch (Exception $e){
echo 'E';
break;
}
exit();
}

?>
<html>
<style>
html{
	background:url('bg.jpg') no-repeat center center fixed;
	background-color: black;
	-webkit-background-size: cover;
	-moz-background-size: cover;
	-o-background-size: cover;
	background-size: cover;
}
input[type=text], input[type=password]{
	border-radius: 6px;
	outline: none;
	border: 1px solid #F0FFD1;
	transition-duration: 2s; 
	width: 70px;
	position: relative;
	left: 5px;
	top: 10px;
	margin-top: 3px;
	background-color: #292828;
	color: #26FFFF;
}
input[type=text]:focus, input[type=password]:focus{
	width: 120px;
}
#login{
	position: relative;
	top: 200px;
	width: 150px;
	height: 100px;
	background-color: #FFFB21;
	opacity: .8;
	border-radius: 18px;
	border: 3px solid #080808;
	
}
input[type=submit]{
	color: #5E5D00;
	border: 2px solid #FFFFFF;
	border-radius: 3px;
	outline: none;
	background-color: #FFFB21;
	position: relative;
	left: -5px;
	top: 10px;
	float: right;
}
input[type=submit]:hover{
	background-color: #FFFFFF;
	color: #0A0A0A;
	border: 2px solid #9C9900;
}
</style>
<center>
<script src="jquery-1.11.2.min.js" type="text/javascript"></script>
<script src="core-min.js" type="text/javascript"></script>
<script src="x64-core-min.js" type="text/javascript"></script>
<script src="sha512-min.js" type="text/javascript"></script>
<script type="text/javascript" src="toastr.js"></script>
<link rel="Stylesheet" href="toastr.css"/>

<form method="POST" name="login" id="login" action="<?php echo $_SERVER['PHP_SELF'];?>">
<input type="text" name="username" placeholder="Username" maxlength="40"><br>
<input type="password" name="password"><br><br>
<input type="submit" value="Login">
</form>
<script>
$(function(){
$('#login').on('submit', function(e){
	e.preventDefault();
	var form = $("#login").get(0);
	var elements = form.querySelectorAll("[type=text]");
	if (elements.length == 0) return alert('An unexpected error occured[1].');
	var username = elements[0].value;
	elements = form.querySelectorAll("[type=password]");
	if (elements.length == 0) return alert('An unexpected error occured[1].');
	var password = elements[0].value;
	var passhashed = CryptoJS.SHA512(password);
	var buffer = 'username=' + encodeURIComponent(username) + '&phashed=' + encodeURIComponent(passhashed);
	$.ajax({
		type: 'post',
		url: $('#login').get(0).action,
		data: buffer,
		success: function(data){
			switch (data){
				case '0':
				toastr.error('Account does not exist.', 'Error.');
				break;
				case '1':
				toastr.error('Incorrect password.', 'Error.');
				break;
				case '2':
				toastr.error('Your account has been suspended.', 'Banned.');
				break;
				case '3':
				toastr.success('You have been logged in.', 'Success!');
				setTimeout(function(){window.location="panel.xtc"}, 2000);
				break;
				case '4':
				toastr.error('You have used up the maximum login attempts allowed.', 'Wait 30 minutes.');
				break;
			}
			
		}
	});
});
});
    </script>
</center>
</html>