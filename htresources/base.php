<?PHP
require $_SERVER['DOCUMENT_ROOT']."/../htresources/config.php";
// localStorage.clear();
?>
<script id="init">

window.addEventListener('error', function (error) {
	reportError(error);
});

function reportError(error) {
	// var string = error.message.toLowerCase();
	// var substring = "script error";
	// if (string.indexOf(substring) > -1){
	// 	console.warn('Script Error: See Browser Console for Detail');
	// } else {
		console.warn(error);
		fetch("/errors.php",
			{
				method: 'POST',
				body: "error="+JSON.stringify({
					message: error.message,
					stack: error.error.stack,
					line: error.lineno,
					column: error.colno,
					srcElement: error.srcElement.constructor.name,
					target: error.target.constructor.name,
					returnValue: error.returnValue,
					timeStamp: error.timeStamp,
					type: error.type,
				}),
				headers:{
					'Content-Type': 'application/x-www-form-urlencoded' //otherwist $_REQUEST is empty
				}
			}
		).then(response => response.text())
		.catch(error => console.error('Error:', error))
		.then(response => console.log('Successfuly reported error:', response));
	// }
}

function hex(buffer) {
	var hexCodes = [];
	var view = new DataView(buffer);
	for (var i = 0; i < view.byteLength; i += 4) {
		// Using getUint32 reduces the number of iterations needed (we process 4 bytes each time)
		var value = view.getUint32(i)
		// toString(16) will give the hex representation of the number without padding
		var stringValue = value.toString(16)
		// We use concatenation and slice for padding
		var padding = '00000000'
		var paddedValue = (padding + stringValue).slice(-padding.length)
		hexCodes.push(paddedValue);
	}

	// Join all the hex strings into one
	return hexCodes.join("");
}

function verifyBootstraps() {
	var validJS = true;
	try {
		new Function(localStorage.bootstrap)
	} catch(err) {
		validJS = false;
	}

	return (
		(localStorage.bootstrap !== undefined && validJS)
		&&
		(localStorage.bootstrapVersion !== undefined)
	);
}

if(!verifyBootstraps()) {
	initialSocket = new WebSocket("ws:"+document.location.hostname+":<?PHP echo SOCKET_PORT; ?>/socket.php");
	initialSocket.onopen = function (event) {
		initialSocket.send(JSON.stringify({type: <?PHP echo PACKET_TYPE_REQUEST_BOOTSTRAP; ?>, data: (localStorage.bootstrapVersion !== undefined?localStorage.bootstrapVersion:"")}));
		// initialSocket.send(JSON.stringify({type: <?PHP echo PACKET_TYPE_REQUEST_HTML; ?>, data: localStorage.htmlVersion}));
		// initialSocket.send(JSON.stringify({type: <?PHP echo PACKET_TYPE_REQUEST_CSS; ?>, data: localStorage.cssVersion}));
	}
	initialSocket.tryClose = function () {
		if(verifyBootstraps()) {
			// console.log("tryclose");
			initialSocket.close();
			delete initialSocket;
		}
	}
	initialSocket.onmessage = function (event) {
		var data = JSON.parse(event.data).data;
		switch(JSON.parse(event.data).type) {
			case <?PHP echo PACKET_TYPE_BOOTSTRAP; ?>:
				// debugger;
				localStorage.bootstrap=data;
				eval(data);
				// debugger;
				break;
			case <?PHP echo PACKET_TYPE_REQUEST_BOOTSTRAP; ?>:
				var buffer = new TextEncoder("utf-8").encode(localStorage.bootstrap);
				// crypto.subtle.digest("SHA-256", buffer).then((buffer)=>initialSocket.send(JSON.stringify({type: <?PHP echo PACKET_TYPE_BOOTSTRAP; ?>, data: new TextDecoder("utf-8").decode(buffer)})));
				crypto.subtle.digest("SHA-256", buffer).then((buffer)=>initialSocket.send(JSON.stringify({type: <?PHP echo PACKET_TYPE_BOOTSTRAP; ?>, data: hex(buffer)})));
				break;
			// case <?PHP echo PACKET_TYPE_REQUEST_BOOTSTRAP; ?>:
			// 	initialSocket.send(JSON.stringify({type: <?PHP echo PACKET_TYPE_BOOTSTRAP; ?>, data: (localStorage.bootstrap!=undefined?localStorage.bootstrap:"")}))
			// 	break;
		}
		initialSocket.tryClose();
	}
	initialSocket.onerror = function (error) {
		reportError(error);
	}
} else {
	var script = document.createElement('script');
	script.id = "bootstrap";
	script.innerHTML = localStorage.bootstrap;
	document.head.appendChild(script);
}
</script>
