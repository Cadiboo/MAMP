<?PHP
require $_SERVER['DOCUMENT_ROOT']."/resource/config.php";
$version = "0.0.0";
switch (mb_strtolower($_REQUEST['type'])) {
  case "bootstrap":
    $version = "0.0.0";
    $version = '"'.time().'"';
    checkVersion();
    loadBootstrap();
    break;
  case "html":
    $version = "0.0.0";
    $version = '"'.time().'"';
    checkVersion();
    loadHTML();
    break;
  case "css":
    $version = "0.0.0";
    $version = '"'.time().'"';
    checkVersion();
    loadCSS();
    break;
    default: die('valid types are ["bootstrap", "html", "css"]');
}
function checkVersion() {
  global $version;
  if(str_replace(".","",$_REQUEST['version']) == str_replace(".","",$version)) {
    die("");
  }
}

function loadBootstrap() {
  global $version;
  echo "localStorage.bootstrapVersion = ".json_encode($version).";\n";
?>
/* Hacks to make better For Loops */
HTMLCollection.prototype.forEach = Array.prototype.forEach;
NodeList.prototype.forEach = Array.prototype.forEach;
Object.prototype.forEach = Array.prototype.forEach;
/* Hack for .insertAfter */
Object.prototype.insertAfter = function (newNode) { this.parentNode.insertBefore(newNode, this.nextSibling); }

async function updateHTML() {
  const html = await fetch('/resource/bootstrap.php?type=html&version='+localStorage.htmlVersion, {method: 'post', mode: 'no-cors'}).then(r=>r.text());
  if(html!="") {
    localStorage.html = html;
    localStorage.htmlVersion = localStorage.html.split("<!--version=\"")[1].split("\"-->")[0];
  }
}
async function updateCSS() {
  const css = await fetch('/resource/bootstrap.php?type=css&version='+localStorage.cssVersion, {method: 'post', mode: 'no-cors'}).then(r=>r.text());
  if(css!="") {
    localStorage.css = css;
    localStorage.cssVersion = css;
    localStorage.cssVersion = localStorage.css.split("/* version=\"")[1].split("\" */")[0];
  }
}
document.addEventListener("DOMContentLoaded", function(event) {
  console.error('Ignore WebSocket Errors like "Broken pipe" or "Connection reset by peer", they\'re expected errors likely due to my abrupt closing of the connection')
  document.body.innerHTML = localStorage.html;
  var style = document.createElement("style");
  style.appendChild(document.createTextNode(localStorage.css));
  document.head.appendChild(style);
  updateHTML();
  updateCSS();
});

const TYPES = new Object({
    ERROR: <?PHP echo PACKET_TYPE_ERROR;?>
    ,SIGNUP: <?PHP echo PACKET_TYPE_SIGNUP;?>
    ,LOGIN: <?PHP echo PACKET_TYPE_LOGIN;?>
    ,NORMAL: <?PHP echo PACKET_TYPE_NORMAL;?>
});

const ERRORS = new Object({
    DO_LOGIN: <?PHP echo ERROR_PACKET_DO_LOGIN;?>
    ,INVALID_LOGIN: <?PHP echo ERROR_PACKET_INVALID_LOGIN;?>
    ,INTERNAL: -1
});

function encrypt(plaintext) {
  return plaintext;//encodeURIComponent(window.btoa(plaintext));
}
function decrypt(ciphertext) {
  return ciphertext;//window.atob(decodeURIComponent(ciphertext));
}

window.socket = null;
window.quedPackets = [];
function socketDaemon() {
  if(!socket || !socket.readyState || socket.readyState!=1) {
    socket = new WebSocket("ws:"+document.location.hostname+":<?PHP echo SOCKET_PORT; ?>/socket.php");
    socket.sendPackets = function() {
      var size = window.quedPackets.length;
      for(i=0; i<size; i++) {
        if(socket.readyState==1)
          socket.send(window.quedPackets.shift());
      }
    }

    socket.quePacket = function(packet) {
      if(packet!="undefined")
        window.quedPackets.push(packet);
    }

    socket.addEventListener("open", function(event){
      socket.quePacket(createPacket(TYPES.NORMAL, "hello"));
      socket.quePacket(createPacket(TYPES.ERROR, "error"));
      socket.sendPackets();
    });

    socket.send = function (packet) {
      WebSocket.prototype.send.apply(this, [encrypt(packet)]);
    }

    socket.addEventListener("message", function(msg) {
      document.body.appendChild(document.createTextNode("msg.data: "+msg.data));
      document.body.appendChild(document.createElement("br"));
      handlePacket(decrypt(msg.data));
    });

    socket.addEventListener("close", function(event){
    });

    socket.addEventListener("error", function(event){
    });

  }
}
socketDaemon();
socketDaemonInterval = setInterval(()=>socketDaemon(), <?PHP echo SOCKET_RECONNECT_TIMEOUT;?>);

function handlePacket(pkt) {
  var packet = JSON.parse(pkt);
  switch (packet.type) {
    case TYPES.ERROR:
      handleError(packet.data);
      break;
    default:
    console.log(packet);
  }
  document.body.appendChild(document.createTextNode("packet: "+JSON.stringify(packet)));
  document.body.appendChild(document.createElement("br"));
}

function handleError(error) {
  var errorDataList = error.split(":");
  var errorCode = errorDataList.shift();
  var errorData = errorDataList.join(":");
  switch (code) {
    case ERRORS.DO_LOGIN:
      console.log("Login Required!");
      if(document.location.pathname != "/login/" && document.location.pathname != "/signup/")
        document.location = "/login/";
      break;
    case ERRORS.INVALID_SIGNUP:
      console.log("Invalid Signup!");
      console.log(errorData);
      break;
    case ERRORS.INVALID_LOGIN:
      console.log("Invalid Login!");
      break;
    default:
    console.log("Unknown Error: "+data);
  }
}

function createPacket(type, message) {
  return JSON.stringify({type: type, message: message});
}

function signup() {
  socket.quePacket(createPacket(TYPES.SIGNUP, "herro"));
  socket.sendPackets();
  return false;
}

document.addEventListener("DOMContentLoaded", function(event) {
  document.getElementById("signup_form").addEventListener("submit", function(event) {
    return false;
  });
  document.getElementById("signup_form").addEventListener("submit", function(event) {
    signup();
  });
});

<?php
}

function loadHTML() {
  global $version;
  return "<!--version=".json_encode($version)."-->\n".
?>
<!DOCTYPE html>
<body>
yeet
</body>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
</head>
<body>
    <div class="wrapper">
        <h2>Sign Up</h2>
        <form id="signup_form" onsubmit="return false;" action="/signup/" method="post">
            <div id="username_holder">
                <label>Username</label>
                <input type="text" name="username"class="form-control" value="<?php echo $username; ?>">
                <span id="username_error"></span>
            </div>
            <div id="password_holder">
                <label>Password</label>
                <input type="password" name="password" class="form-control" value="<?php echo $password; ?>">
                <span id="password_error"></span>
            </div>
            <div id="confirm_password_holder">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" value="<?php echo $confirm_password; ?>">
                <span id="confirm_password_error"></span>
            </div>
            <div id="submit_holder">
                <input type="submit" id="submit" value="Submit">
                <input type="reset" id="reset" value="Reset">
            </div>
            <p>Already have an account? <a href="/login/">Login here</a>.</p>
        </form>
    </div>
</body>
</html>
<?php
;
}

function loadCSS() {
  global $version;
  echo "/* version=".json_encode($version)." */\n\n";
?>
* {
  background: gainsboro;
}
<?php
}
?>
