<?php
Header('X-Frame-Options: DENY');

if (isset($_POST['username']) && isset($_POST['phashed'])){
if (empty($_POST['username']) || empty($_POST['phashed'])) exit;
if (preg_match('/[^\p{Latin}\d ]/u', $_POST['username'])) Die('-1');
try{
require('cfg.php');
$conn = new PDO("mysql:host=localhost; dbname=$dbname", $dbuser, $dbpass);
$c = $conn->Prepare("SELECT `username` FROM `accounts` WHERE `regip` = ?");
$c->Execute(Array($_SERVER['REMOTE_ADDR']));
$r = $c->Fetch(PDO::FETCH_ASSOC);
if ($r) die('2');
/*http://stackoverflow.com/a/18899561*/
$length = 10;
$salt = base64_encode(mcrypt_create_iv(ceil(0.75*$length), MCRYPT_DEV_URANDOM));
$pw = hash('sha512', $_POST['phashed'] . $salt, false);
$c = $conn->Prepare("INSERT INTO `accounts` (`regip`, `username`, `password`, `lastlogin`, `sessions`, `privileges`, `salt`) VALUES (?, ?, ?, 0, 0, 1, ?)");
$r = $c->Execute(Array(ip2long($_SERVER['REMOTE_ADDR']), $_POST['username'], $pw, $salt));
if (!$r) die('1');
$c = $conn->Prepare("INSERT INTO `balances` (`username`) VALUES (?)");
$c->Execute(Array($_POST['username']));
die('3');
}catch (Exception $e){
echo 'E';
break;
}
exit();
}

?>

<html>
<meta charset="utf-8">
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
	margin-bottom: 5px;
	background-color: #292828;
	color: #26FFFF;
}
input[type=text]:focus, input[type=password]:focus{
	width: 120px;
}
#register{
	position: relative;
	top: 200px;
	width: 150px;
	height: 125px;
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
	top: -20px;
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
<script type="text/javascript" src="strength.min.js"></script>
<link rel="Stylesheet" href="toastr.css"/>
<link rel="stylesheet" type="text/css" href="strength.css">

<form method="POST" name="register" id="register" action="<?php echo $_SERVER['PHP_SELF'];?>" accept-charset="utf-8">
<input type="text" name="username" placeholder="Username" maxlength="40"><br>
<input type="password" name="password" id="pw" value=""><br><br>
<input type="submit" value="Register">
</form>
<script>
$(function(){
$('#register').on('submit', function(e){
	e.preventDefault();
	var form = $("#register").get(0);
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
		url: $('#register').get(0).action,
		data: buffer,
		success: function(data){
			switch (data){
				case '-1':
				toastr.error('Invalid username.', 'Error.');
				break;
				case 'E':
				toastr.error('An error occured.', 'Error.');
				break;
				case '1':
				toastr.error('Username already taken.', 'Error.');
				break;
				case '2':
				toastr.error('You have already created an account with this IP address.', 'Error.');
				break;
				case '3':
				toastr.success('You have been registered.', 'Success!');
				setTimeout(function(){window.location="login.php"}, 1000);
				break;
			}
			
		}
	});
});
});

$(document).ready(function($) {
	
$('#pw').strength({
            strengthClass: 'strength',
            strengthMeterClass: 'strength_meter',
            strengthButtonClass: 'button_strength',
            strengthButtonText: '',
            strengthButtonTextToggle: ''
        });
});
</script>
</center>
</html>