<?php
require $_SERVER['DOCUMENT_ROOT']."/../htresources/config.php";
?>
/* Hacks to make better For Loops */
HTMLCollection.prototype.forEach = Array.prototype.forEach;
NodeList.prototype.forEach = Array.prototype.forEach;
Object.prototype.forEach = Array.prototype.forEach;
/* Hack for .insertAfter */
Object.prototype.insertAfter = function(newNode) {
	this.parentNode.insertBefore(newNode, this.nextSibling);
}

const TYPES = new Object({
	 ERROR: <?PHP echo PACKET_TYPE_ERROR;?>
	,REQUEST_BOOTSTRAP: <?PHP echo PACKET_TYPE_REQUEST_BOOTSTRAP;?>
	,REQUEST_HTML: <?PHP echo PACKET_TYPE_REQUEST_HTML;?>
	,REQUEST_CSS: <?PHP echo PACKET_TYPE_REQUEST_CSS;?>
	,BOOTSTRAP: <?PHP echo PACKET_TYPE_BOOTSTRAP;?>
	,HTML: <?PHP echo PACKET_TYPE_HTML;?>
	,CSS: <?PHP echo PACKET_TYPE_CSS;?>
	,PAGE_SCRIPT: <?PHP echo PACKET_TYPE_PAGE_SCRIPT;?>
	,PAGE_HTML: <?PHP echo PACKET_TYPE_PAGE_HTML;?>
	,PAGE_CSS: <?PHP echo PACKET_TYPE_PAGE_CSS;?>
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
	return encodeURIComponent(window.btoa(plaintext));
	// return plaintext;
}

function decrypt(ciphertext) {
	return window.atob(decodeURIComponent(ciphertext));
	// return ciphertext
}

function getQuedPackets() {
	if(localStorage.quedPackets == undefined || !JSON.parse(localStorage.quedPackets)) {
		localStorage.quedPackets = JSON.stringify([]);
	}
	return JSON.parse(localStorage.quedPackets);
}

function setQuedPackets(quedPackets) {
	localStorage.quedPackets = JSON.stringify(quedPackets);
}

window.socket = null;
function socketDaemon() {
	if(!socket || !socket.readyState || socket.readyState!=1) {
		socket = new WebSocket("ws:"+document.location.hostname+":<?PHP echo SOCKET_PORT; ?>/socket.php");
		socket.sendPackets = function() {
			var quedPackets = getQuedPackets();
			for(i=0; i<quedPackets.length; i++) {
				if(socket.readyState==1)
					socket.send(quedPackets.shift());
			}
			setQuedPackets(quedPackets);
		}

		socket.quePacket = function(packet) {
			if(packet !== undefined) {
				var quedPackets = getQuedPackets();
				quedPackets.push(packet);
				setQuedPackets(quedPackets);
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
			try {
				JSON.parse(msg.data);
				handlePacket(msg.data);
			} catch (e) {
				handlePacket(decrypt(msg.data));
			}
		});

		socket.addEventListener("close", function(event) {
			handleConnectivity();
		});

		socket.addEventListener("error", function(error) {
			reportError(error);
		});
	}
}

function handleConnectivity() {
	if(!socket || socket.readyState>1) {
		var span = document.createElement('span');
	  span.appendChild(document.createTextNode("OFFLINE!"));
  	span.style.color = "red";
		document.body.appendChild(span);
		document.body.appendChild(document.createElement("br"));
	}
}

function updateBootstraps() {
	socket.quePacket(createPacket(TYPES.REQUEST_BOOTSTRAP, localStorage.bootstrapVersion));
	socket.quePacket(createPacket(TYPES.REQUEST_HTML, localStorage.htmlVersion));
	socket.quePacket(createPacket(TYPES.REQUEST_CSS, localStorage.cssVersion));
	socket.sendPackets();
}

function handlePacket(pkt) {
	var packet = JSON.parse(pkt);
	console.log(pkt);
	console.log(packet);
	switch (packet.type) {
		case TYPES.ERROR:
			handleError(packet.data);
			break;
		case TYPES.BOOTSTRAP:
			var old = localStorage.bootstrap;
			localStorage.bootstrap=packet.data;
			eval(packet.data);
			console.log("Recieved Bootstrap update "+localStorage.bootstrapVersion);
			if(old != undefined)
				update(false);
			break;
		case TYPES.REQUEST_BOOTSTRAP:
				crypto.subtle.digest("SHA-256", new TextEncoder("utf-8").encode(localStorage.bootstrap)).then((buffer)=>socket.send(JSON.stringify({type: TYPES.BOOTSTRAP, data: hex(buffer)})));
			break;
		case TYPES.HTML:
			var old = localStorage.html;
			localStorage.html=packet.data;
			localStorage.htmlVersion = localStorage.html.split("version=\"")[1].split("\"")[0];
			console.log("Recieved HTML update "+localStorage.htmlVersion);
			if(old != undefined)
				update(false);
			break;
		case TYPES.REQUEST_HTML:
				crypto.subtle.digest("SHA-256", new TextEncoder("utf-8").encode(localStorage.html)).then((buffer)=>socket.send(JSON.stringify({type: TYPES.HTML, data: hex(buffer)})));
			break;
		case TYPES.CSS:
			var old = localStorage.css;
			localStorage.css=packet.data;
			localStorage.cssVersion = localStorage.css.split("version=\"")[1].split("\"")[0]
			console.log("Recieved CSS update "+localStorage.cssVersion);
			if(old != undefined)
				update(false);
			break;
		case TYPES.REQUEST_CSS:
				crypto.subtle.digest("SHA-256", new TextEncoder("utf-8").encode(localStorage.css)).then((buffer)=>socket.send(JSON.stringify({type: TYPES.CSS, data: hex(buffer)})));
			break;
		default:
		console.log("packet:");
		console.log(packet);
	}
	document.body.appendChild(document.createTextNode("packet: "+JSON.stringify(packet)));
	document.body.appendChild(document.createElement("br"));
}

function update(force=false) {
	if(force) {
		alert("Sucessfully Updated Application, A reload is required to use the application")
		window.location.reload();
	} else {
		if(confirm("Sucessfully Updated Application. The Update will not come into effect until the page is reloaded. Reload now?"))
			window.location.reload();
	}
}

function handleError(error) {
	var errorDataList = error.split(":");
	var errorCode = errorDataList.shift();
	var errorData = errorDataList.join(":");
	switch (errorCode) {
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
		console.error("Unknown Error: "+errorData);
	}
}

function createPacket(type, data, plaintext) {
	if(data === undefined)
		data = "";
	return JSON.stringify(plaintext === true?{type: type, data: data, plaintext: true}:{type: type, data: data});
}

function loadHTML(html) {
	document.body.innerHTML = html;
}

function loadSignupHTML() {
	document.body.innerHTML = `signup`;
}

function loadLoginHTML() {
	document.body.innerHTML = `login`;
}

document.addEventListener("DOMContentLoaded", function(event) {
		// console.error('Ignore WebSocket Errors like "Broken pipe" or "Connection reset by peer", they\'re expected errors likely due to my abrupt closing of the connection')
	socketDaemon();
	socketDaemonInterval = setInterval(()=>socketDaemon(), <?PHP echo SOCKET_RECONNECT_TIMEOUT;?>);
	updateBootstraps();
	updateBootstrapsInterval = setInterval(()=>updateBootstraps(), <?PHP echo BOOTSTRAP_UPDATE_TIMEOUT;?>);
});

// function signup() {
// 	socket.quePacket(createPacket(TYPES.SIGNUP, "herro"));
// 	socket.sendPackets();
// 	return false;
// 	}
//
// 	document.addEventListener("DOMContentLoaded", function(event) {
// 	document.getElementById("signup_form").addEventListener("submit", function(event) {
// 		return false;
// 	});
// 	document.getElementById("signup_form").addEventListener("submit", function(event) {
// 		signup();
// 	});
// });
