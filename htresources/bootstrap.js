/* Hacks to make better For Loops */
HTMLCollection.prototype.forEach = Array.prototype.forEach;
NodeList.prototype.forEach = Array.prototype.forEach;
Object.prototype.forEach = Array.prototype.forEach;
/* Hack for .insertAfter */
Object.prototype.insertAfter = function (newNode) { this.parentNode.insertBefore(newNode, this.nextSibling); }

const TYPES = new Object({
     ERROR: <?PHP echo PACKET_TYPE_ERROR;?>
    ,REQUEST_BOOTSTRAP: <?PHP echo PACKET_TYPE_REQUEST_BOOTSTRAP;?>
    ,REQUEST_HTML: <?PHP echo PACKET_TYPE_REQUEST_HTML;?>
    ,REQUEST_CSS: <?PHP echo PACKET_TYPE_REQUEST_CSS;?>
    ,BOOTSTRAP: <?PHP echo PACKET_TYPE_BOOTSTRAP;?>
    ,HTML: <?PHP echo PACKET_TYPE_HTML;?>
    ,CSS: <?PHP echo PACKET_TYPE_CSS;?>
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
function socketDaemon() {
  console.log(socket);
  if(!socket || !socket.readyState || socket.readyState!=1) {
    socket = new WebSocket("ws:"+document.location.hostname+":<?PHP echo SOCKET_PORT; ?>/socket.php");
    if(!localStorage.quedPackets || localStorage.quedPackets=="undefined") {
      localStorage.quedPackets = JSON.stringify([]);
    }
    socket.sendPackets = function() {
      var quedPackets = JSON.parse(localStorage.quedPackets);
      var size = quedPackets.length;
      for(i=0; i<size; i++) {
        if(socket.readyState==1)
          socket.send(quedPackets.shift());
      }
      localStorage.quedPackets = JSON.stringify(quedPackets);
    }

  socket.quePacket = function(packet) {
    if(packet!="undefined") {
      var quedPackets = JSON.parse(localStorage.quedPackets);
      quedPackets.push(packet);
      localStorage.quedPackets = JSON.stringify(quedPackets);
    }
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

function updateBootstraps() {
  socket.quePacket(createPacket(TYPES.REQUEST_BOOTSTRAP, localStorage.bootstrapVersion));
  socket.quePacket(createPacket(TYPES.REQUEST_HTML, localStorage.htmlVersion));
  socket.quePacket(createPacket(TYPES.REQUEST_CSS, localStorage.cssVersion));
}
updateBootstraps();
updateBootstrapsInterval = setInterval(()=>updateBootstraps(), <?PHP echo SOCKET_RECONNECT_TIMEOUT;?>);

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

// function signup() {
//   socket.quePacket(createPacket(TYPES.SIGNUP, "herro"));
//   socket.sendPackets();
//   return false;
// }
//
// document.addEventListener("DOMContentLoaded", function(event) {
//   document.getElementById("signup_form").addEventListener("submit", function(event) {
//     return false;
//   });
//   document.getElementById("signup_form").addEventListener("submit", function(event) {
//     signup();
//   });
// });
