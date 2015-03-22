<?php
if (!isset($_COOKIE['cookie'])){
	Header("Location: login.php");
	Exit();
}
$cookie = $_COOKIE['cookie'];
try{
require('cfg.php');
$conn = new PDO("mysql:host=localhost; dbname=$dbname", $dbuser, $dbpass);
$c = $conn->Prepare("SELECT `csrf`, `ip`, `username` FROM `sessions` WHERE `cookie` = ?");
$c->Execute(Array($cookie));
$r = $c->Fetch(PDO::FETCH_ASSOC);
if ($r == null){
	Header("Location: login.php");
	Exit();
}
$username = $r['username'];
$csrftoken = $r['csrf'];

if ($r['ip'] != ip2long($_SERVER['REMOTE_ADDR'])){
	$c = $conn->Prepare("DELETE FROM `sessions` WHERE `cookie` = ?");
	$c->Execute(Array($cookie));
	$c = $conn->Prepare("UPDATE `accounts` SET `sessions` = `sessions` - 1 WHERE `username` = ?");
	$c->Execute(Array($r['username']));
	Header("Location: login.php");
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

$c = $conn->Prepare("SELECT COUNT(*) FROM `accounts`");
$c->Execute();
$regcount = $c->FetchColumn(0);
$c = $conn->Prepare("SELECT COUNT(*) FROM `sessions` WHERE `lastactive` >= ?");
$c->Execute(Array(Time() - 2*60));//15 minutes.
$online = $c->FetchColumn(0);

$c = $conn->Prepare("SELECT COUNT(*) FROM `bets`");
$c->Execute();//15 minutes.
$totalbets = $c->FetchColumn(0);
?>
<html>
<body>
<script src="jquery-1.11.2.min.js" type="text/javascript"></script>
<script type="text/javascript" src="toastr.js"></script>
<link rel="Stylesheet" href="toastr.css"/>
<style>
html{
background-color: #000000;
background-image: url('lovely.png');
}

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
left: 990px;
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

#sidebar{
	position: relative;
	top: 20px;
	left: 10px;
	height: 500px;
	width: 200px;
	background-color: black;
	overflow: auto;
}

.user{
	color: #29E3AC;
	border: 2px solid #595959;
	border-radius: 5px;
	position: relative;
	left: 10px;
	top: 20px;
	padding-bottom: 7px;
	padding-left: 7px;
	padding-right: 7px;
	padding-top: 7px;
	display:inline-block;
	margin-top: 7px;
	margin-right: 5px;
}
.user:hover{
	border: 2px solid #FC0076;
	cursor: pointer;
}
.user:active{
	border: 2px solid #00E3FC;
}
.useramt{
	font-size: 10px;
	color: #00FF08;
	position: relative;
	top: -8px;
	left: -3px;
	float: left;
}

#mychallenges{
	width: 300px;
	height: 300px;
	position: absolute;
	left: 250px;
	top: 20px;
	background-color: black;	
}
#tchallenges{
	width: 300px;
	height: 300px;
	position: absolute;
	left: 650px;
	top: 20px;
	background-color: black;	
}

#chatbox{
	width: 1000px;
	height: 300px;
	position: absolute;
	left: 250px;
	top: 330px;
	background-color: black;
	opacity: .8;
	overflow: auto;
}

/******************replyleft reply[begin]*************************/
.replyleftcontainer{
position: relative;
width: 810px;
height: 100px;
left: 80px;
}
.replyleftname{
  background: #f3961c;
  border-radius: 12px;
  width: auto;
  padding-right: 6px;
  padding-left: 6px;
  height: 20px;
  position: absolute;
  left: -60px;
  top: -5px;
  text-align:center;
  color: #00FFFF;
  font-family: Monaco, Consolas, "Lucida Console", monospace;
}

.replyleftreply{
  word-wrap:break-word;
  position: relative;
  left: 55px;
  width: 700px;
  padding: 15px;
  height: 50px;
  margin: 1em 0 3em;
  color: #FF0099;//#00FFFF;
  font-family: "Rockwell Extra Bold", "Rockwell Bold", monospace;
  font-weight: bold;
  background: #f3961c;
  border-radius: 10px;
  background: linear-gradient(top, #f9d835, #f3961c);
}

.replyleftreply:after {
  content: "";
  display: block;
  position: absolute;
  bottom: 30px;
  left: -60px;
  border-width: 30px 0px 0px 50px;
  border-style: solid;
  border-color: #f3961c transparent;
}

/******************replyleft[end]*************************/
/******************replyright[begin]*************************/
.replyrightcontainer{
position: relative;
width: 810px;
height: 100px;
left: 80px;
}
.replyrightinfo{
  position: absolute;
  height: 12px;
  position: absolute;
  left: 20px;
  top: 0px;
  font-size: 10px;
  border-radius: 5px;
  background: black;
  color: red;
}
.replyrightname{
  background: #f3961c;
  border-radius: 12px;
  //width: 50px;
  width: auto;
  padding-right: 6px;
  padding-left: 6px;
  height: 20px;
  position: absolute;
  left: 800px;
  top: 5px;
  text-align:center;
  color: #2B00FF;
  font-family: Monaco, Consolas, "Lucida Console", monospace;
}

.replyrightreply{
  word-wrap:break-word;
  position: relative;
  left: 0px;
  width: 700px;
  padding: 15px;
  height: 50px;
  margin: 1em 0 3em;
  color: white; //#2B00FF;
  font-family: "Rockwell Extra Bold", "Rockwell Bold", monospace;
  font-weight: bold;
  background: #f3961c;
  border-radius: 10px;
  background: linear-gradient(top, #f9d835, #f3961c);
}

.replyrightreply:after {
  content: "";
  display: block;
  position: absolute;
  bottom: 30px;
  left: 740px;
  border-width: 30px 50px 0px 0px;
  border-style: solid;
  border-color: #f3961c transparent;
}

/******************replyright[end]*************************/

#reply{
position: relative;
left: 250px;
top: 130px;
background: black;
opacity: .7;
width: 1000px;
height: 150px;
}
a{
text-decoration: none;
color: cyan;
}
a:hover{
color: red;
}
/*************************/


.challenge{
	color: #29E3AC;
	border: 2px solid pink;
	border-radius: 5px;
	position: relative;
	left: 10px;
	top: 20px;
	padding-bottom: 7px;
	padding-left: 7px;
	padding-right: 7px;
	padding-top: 7px;
	display:inline-block;
	margin-top: 7px;
	margin-right: 5px;
}

</style>
</body>
<title>OMC PVP</title>

<div class="stats">
<center>
<b style="color: #AAFF00">~Stats~</b>
<p style="color: #AAFF00"><?php echo strval($regcount); ?> registered users.</p>
<p style="color: #AAFF00"><?php echo strval($online); ?> online users.</p>
<p style="color: #AAFF00"><?php echo strval($totalbets);?> bets.</p>
<p style="color: #AAFF00">0 games.</p>
</center>

</div>

<div id="sidebar"> <!--maximum 24 slots before overflow!-->
<?php
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
	$id2 = $id . "b";
	echo "<p class=\"user\" id=\"$id\" onclick=challenge(this)>";
	echo "<the class=\"useramt\" id=\"$id2\">";
	echo $r['balance'];
	echo "</the>";
	echo htmlspecialchars($session['username']);
	echo "</p>";
}
?>
</div>

<div id="mychallenges">
<b style="color: orange; position: relative; left: 100px; top: 12px;">Your Challenges</b>
<br>
</div>

<div id="tchallenges">
<b style="color: orange; position: relative; left: 115px; top: 12px;">Challenges</b>
<br>
</div>

<div id="chatbox">
<?php
$left = false;
$tick = Time();
$lastuser = "";
foreach ($conn->Query("SELECT * FROM `chat` ORDER BY `date` DESC LIMIT 0,24") as $msg){
	if ($lastuser != $msg['user'])
	{
		$left = !$left;
		$lastuser = $msg['user'];
	}
	if ($left){
		echo "<div class=\"replyleftcontainer\">" .
		"<div class=\"replyleftname\">" . htmlspecialchars($msg['user']) . "</div>" .
'<div class="replyleftreply">' . htmlspecialchars($msg['msg']) . '</div></div>';
	}
	else{
		echo "<div class=\"replyrightcontainer\">" .
		"<div class=\"replyrightname\">" . htmlspecialchars($msg['user']) . "</div>" .
'<div class="replyrightreply">' . htmlspecialchars($msg['msg']) . '</div></div>';
	}
}

?>
</div>
<script src="custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js"></script>

<link href="custom-scrollbar-plugin/jquery.mCustomScrollbar.css" rel="stylesheet" />
<script src="custom-scrollbar-plugin/jquery.mCustomScrollbar.js"></script>

<script>
/*chat loader*/
var chattick = <?php
echo strval($tick);
?>;
document.getHTMLFull = function(who, deep){
    if(!who || !who.tagName) return '';
    var txt, ax, el= document.createElement("div");
    el.appendChild(who.cloneNode(false));
    txt= el.innerHTML;
    if(deep){
        ax= txt.indexOf('>')+1;
        txt= txt.substring(0, ax)+who.innerHTML+ txt.substring(ax);
    }
    el= null;
    return txt;
}
var chaty = setInterval(function(){
	$.ajax({
		url:'chat.php',
		type:'post',
		data:'tick=' + encodeURIComponent(chattick),
		success: function(data){
				if (data == "") return;
				var chatbox = document.getElementById("chatbox");
				$(chatbox).mCustomScrollbar('destroy');
				//alert(chatbox.innerHTML);
				if (!chatbox){
					clearInterval(chaty);
					return;
				}
				var arrdat = data.split("|");
				chattick = arrdat[0];
				var chats = [];
				var elements = chatbox.getElementsByTagName("div");
				j = 0;
				for (i = 0; i < elements.length; i++)
					if (elements[i].parentNode == chatbox)
						chats[j++] = document.getHTMLFull(elements[i], true);
				//if (j > 9) //chats = chats.splice(0, arrdat.length);
				//chats = chats.splice(chats.length - (arrdat.length - 2), (arrdat.length - 2))
				for (var j = 1; j < arrdat.length; j++)
				{
					var tmp = decodeURIComponent(arrdat[j]).replace(new RegExp("\\+","g"),' ')
					if (tmp == "") continue;
					//chats[chats.length] = tmp;
					//alert(tmp);
					chats.splice(0, 0, tmp);
				}
				chatbox.innerHTML = chats;
				$("#chatbox").mCustomScrollbar({scrollButtons:{enable:true},callbacks:{onScroll:function(){ $("."+this.attr("id")+"-pos").text(mcs.top); }}});
				//$("#chatbox").mCustomScrollbar("scrollTo", "bottom");
		}
	});
}, 2000);
</script>


<!--cool scrollbar-->
<script>
(function($){
$(window).load(function(){
$("#chatbox").mCustomScrollbar({scrollButtons:{enable:true},callbacks:{onScroll:function(){ $("."+this.attr("id")+"-pos").text(mcs.top); }}});
});
})(jQuery);
</script>
<!--cool scrollbar end-->

<style>
form textarea{
  width: 950px;
  height: 100px;
  margin-bottom: 10px;
  padding-left: 15px;
  background: #fff;
  background-color: #010709;
  border: 1px solid #355563;
  color: #aed0e0;
  border: none;
  color: #e74c3c;
  outline: none;
  position: relative;
  left: 15px;
  top: 8px;
  transition: 1s;
}

form textarea:focus {
-webkit-box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.3), 0 0 4px 1px rgba(255, 255, 255, 0.6);
  box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.3), 0 0 4px 1px rgba(255, 255, 255, 0.6);
  margin-bottom: 10px;
  padding-left: 15px;
  background: #fff;
  border: none;
  color: #e74c3c;
  background:#062127;
  outline:none;
}

input[type=submit]{
border-radius: 3px;
border: none;
width: 100px;
height: 20px;
position: relative;
left: 50px;
top: 10px;
transition: 1s;
color: #37BDC4;
background: #474546;
}
input[type=submit]:hover{
border: 2px solid white;
background: black;
color: #00FF95;
}

::-webkit-scrollbar {
    height: 12px;
	width: 12px;
	background: #000;
    }
::-webkit-scrollbar-thumb {
    background: #FF00BF;
    -webkit-border-radius: 1ex;
    -webkit-box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.75);
}
::-webkit-scrollbar-corner {
    background: #000;
}

</style>
<div id="reply">
<form method="POST" id="replyform">
<input type="hidden" name="csrf" value="<?php echo $csrftoken; ?>">;
<textarea name="msg" rows="4" cols="50" maxlength="100" placeholder="Enter Response here"></textarea>
<input type="submit" name="reply" Value="Add Reply">
</form>
<script>
$("#replyform").on("submit", function(e){
	e.preventDefault();
	$.ajax({
		url:"sendmsg.php",
		type:"post",
		data: $("#replyform").serialize(),
		success: function(data){
				$("msg").text("");
		}
	});
});
</script>
</div>


<script>
  var me = "<?php echo htmlspecialchars($username);?>";
  var csrftoken = "<?php echo $csrftoken;?>";
  var balance = <?php
  $c = $conn->prepare("SELECT `balance` FROM `balances` WHERE `username` = ?");
	$c->Execute(Array($username));
	$r = $c->Fetch(PDO::FETCH_ASSOC);
	if ($r == null)
		echo "0";
	else
		echo strval($r['balance']);
  ?>;
  
  function escapeHtml(text) {
  var map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };

  return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

function htmlspecialchars_decode(string, quote_style) {
  //       discuss at: http://phpjs.org/functions/htmlspecialchars_decode/
  //      original by: Mirek Slugen
  //      improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //      bugfixed by: Mateusz "loonquawl" Zalega
  //      bugfixed by: Onno Marsman
  //      bugfixed by: Brett Zamir (http://brett-zamir.me)
  //      bugfixed by: Brett Zamir (http://brett-zamir.me)
  //         input by: ReverseSyntax
  //         input by: Slawomir Kaniecki
  //         input by: Scott Cariss
  //         input by: Francois
  //         input by: Ratheous
  //         input by: Mailfaker (http://www.weedem.fr/)
  //       revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // reimplemented by: Brett Zamir (http://brett-zamir.me)
  //        example 1: htmlspecialchars_decode("<p>this -&gt; &quot;</p>", 'ENT_NOQUOTES');
  //        returns 1: '<p>this -> &quot;</p>'
  //        example 2: htmlspecialchars_decode("&amp;quot;");
  //        returns 2: '&quot;'

  var optTemp = 0,
    i = 0,
    noquotes = false;
  if (typeof quote_style === 'undefined') {
    quote_style = 2;
  }
  string = string.toString()
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>');
  var OPTS = {
    'ENT_NOQUOTES': 0,
    'ENT_HTML_QUOTE_SINGLE': 1,
    'ENT_HTML_QUOTE_DOUBLE': 2,
    'ENT_COMPAT': 2,
    'ENT_QUOTES': 3,
    'ENT_IGNORE': 4
  };
  if (quote_style === 0) {
    noquotes = true;
  }
  if (typeof quote_style !== 'number') { // Allow for a single string or an array of string flags
    quote_style = [].concat(quote_style);
    for (i = 0; i < quote_style.length; i++) {
      // Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
      if (OPTS[quote_style[i]] === 0) {
        noquotes = true;
      } else if (OPTS[quote_style[i]]) {
        optTemp = optTemp | OPTS[quote_style[i]];
      }
    }
    quote_style = optTemp;
  }
  if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
    string = string.replace(/&#0*39;/g, "'"); // PHP doesn't currently escape if more than one 0, but it should
    // string = string.replace(/&apos;|&#x0*27;/g, "'"); // This would also be useful here, but not a part of PHP
  }
  if (!noquotes) {
    string = string.replace(/&quot;/g, '"');
  }
  // Put this in last place to avoid escape being double-decoded
  string = string.replace(/&amp;/g, '&');

  return string;
}


function challengeresult(result, user){
	if (result[1] == "true"){
		toastr.success('You won ' + result[2] +' OMC from '+ htmlspecialchars_decode(user) + ' with roll: ' + result[3] + '!', "Winner!");
		balance += parseFloat(result[2]);
		var e = document.getElementById(me + "b");
		if (e) e.innerText = balance.toString();
		e = document.getElementById(user + "b");
		if (e) e.innerText = parseFloat(e.innerText) - parseFloat(result[2]);
	}
	else if (result[1] == "false"){
		toastr.error('You lost ' + result[2] +' OMC to ' + htmlspecialchars_decode(user) + ' with roll: ' + result[3] + '!', "Loser!");
		balance -= parseFloat(result[2]);
		var e = document.getElementById(me + "b");
		if (e) e.innerText = balance.toString();
		e = document.getElementById(user + "b");
		if (e) e.innerText = parseFloat(e.innerText) + parseFloat(result[2]);
	}
	else if (result[1] == "rejected"){
		toastr.warning('Challenge was rejected by ' + htmlspecialchars_decode(user) + '.', "Rejected!");
	}
	else if (result[1] == "timeout"){
		toastr.warning('Challenge to/from '+ htmlspecialchars_decode(user) +' timed out.', "Failure!");
	}
	else alert('Error');
}

function waitchallenge(id, user, amount){
	var iv;
	var parent = document.getElementById("mychallenges");
	var delement = document.createElement("div");
	delement.className = "challenge";
	parent.appendChild(delement);
	delement.innerHTML = escapeHtml(user) + " => " + amount;
	var tick = 0;
	iv = setInterval(function(){
		  $.ajax({
		  url:"checkchallengestate.php",
		  type: "POST",
		  data: "id=" + encodeURIComponent(id),
		  success: function(data){
			var arr = data.split("|");
			if (arr[0] == "true"){
				clearInterval(iv);
				parent.removeChild(delement);
				challengeresult(arr, user);
			}
			if (arr[0] == "false"){
				 clearInterval(iv);
				 parent.removeChild(delement);
			}
		  }
	  });
	   if (tick == 60){
		   clearInterval(iv);
		   parent.removeChild(delement);
	   }
		tick++;
	}, 1500);
}


<?php
$c = $conn->Prepare("SELECT * FROM `challenges` WHERE (`challenger` = ? AND `time` > ? AND `accepted` = 2)");
$c->Execute(Array($username, Time() - 60));
$r = $c->FetchAll();
foreach ($r as $activechallenge){
	echo "waitchallenge(";
	echo $activechallenge['id'] . ', ';
	echo '"' . htmlspecialchars($activechallenge['challenged']) . '", ';
	echo $activechallenge['amount'] . ');';
}
?>

function ichallenged(id, user, amount){
	var iv;
	var parent = document.getElementById("tchallenges");
	var delement = document.createElement("div");
	delement.className = "challenge";
	delement.id = id;
	parent.appendChild(delement);
	delement.innerHTML = escapeHtml(user) + " => " + amount;
	var tick = 0;
	iv = setInterval(function(){
		  $.ajax({
		  url:"checkchallengestate.php",
		  type: "POST",
		  data: "id=" + encodeURIComponent(id),
		  success: function(data){
			var arr = data.split("|");
			if (arr[0] == "true"){
				clearInterval(iv);
				parent.removeChild(delement);
				challengeresult(arr, user);
			}
			if (arr[0] == "false"){
				 clearInterval(iv);
				 parent.removeChild(delement);
			}
		  }
	  });
	   if (tick == 60){
		   clearInterval(iv);
		   parent.removeChild(delement);
	   }
		tick++;
	}, 2000);
	delement.onclick = function(){
		  clearInterval(iv);
		  parent.removeChild(delement);
		   $.ajax({
		  url:"acceptchallenge.php",
		  type: "POST",
		  data: "id=" + encodeURIComponent(id) + "&csrf=" + encodeURIComponent(csrftoken),
		  success: function(data){
			  challengeresult(data.split("|"), user);
		  }});
		}
	
}


function challengesfrom(){
var lastupdate = <?php
$t = time();
$c = $conn->Prepare("SELECT * FROM `challenges` WHERE `challenged` = ? AND `time` > ? AND `accepted` = 2");
$c->Execute(Array($username, $t - 60));
$r = $c->FetchAll();
echo $t;
?>;

var ic = setInterval(function(){
	 $.ajax({
		  url:"mychallenges.php",
		  type: "POST",
		  data: "lastupdate=" + encodeURIComponent(lastupdate),
		  success: function(data){
			  if (data.length == 0) return;
			if (data[0] == "1"){
				data = data.substring(1, data.length);
				tmp = data.split("|");
				lastupdate = tmp[1];
				for (i = 1; i < tmp.length; i++)
					eval(decodeURIComponent(tmp[i]));
			}
			if (data[0] == "2") clearInterval(ic);
		  }
	 });
}, 3250);

}

<?php
foreach ($r as $challenge){
	echo "ichallenged(";
	echo $challenge['id'] . ', ';
	echo '"' . htmlspecialchars($challenge['challenger']) . '", ';
	echo $challenge['amount'] . ');';
}
?>

challengesfrom();

setInterval(function(){
	$.ajax({
		url:"online.php",
		type:"post",
		data: "null",
		success:function(data){
			var sidebar = document.getElementById("sidebar");
			if (!sidebar.mCustomScrollbar) $("#sidebar").mCustomScrollbar({scrollButtons:{enable:true},callbacks:{onScroll:function(){ $("."+this.attr("id")+"-pos").text(mcs.top); }}});
			//$(sidebar).mCustomScrollbar('destroy');
			sidebar.innerHTML = data;
			$("#sidebar").mCustomScrollbar('update');
		}
	});
}, 5000);

window.challenge = function challenge(user){
  var un = user.id;
  var amount = prompt("OMC to bet?", "1.0");
  if (balance < amount){
	  alert("Your balance is not big enough!");
	  return;
  }
  if (window.confirm("Challenge " + htmlspecialchars_decode(un) + " for " + amount + " OMC?"))
  {
	  buf = "un=" + encodeURIComponent(un) + "&amount=" + encodeURIComponent(amount) + "&csrf=" + encodeURIComponent(csrftoken);
	  $.ajax({
		  url:"challenge.php",
		  type: "POST",
		  data: buf,
		  success: function(dat){
			var arr = dat.split("|");
			if (arr[0] == "true") 
				waitchallenge(arr[1], un, amount);
			else
				alert('Unable to challenge user(possibly there balance is not big enough).');
		  }
	  });
	  
  }
}
</script>

</html>