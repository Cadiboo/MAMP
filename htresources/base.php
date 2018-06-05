<?PHP
require $_SERVER['DOCUMENT_ROOT']."/../htresources/config.php";

// localStorage.bootstrap= "null";
// localStorage.html = "null";
// localStorage.css = "null";
// localStorage.bootstrapVersion = "null";
// localStorage.htmlVersion      = "null";
// localStorage.cssVersion       = "null";
?>
<script id="bootstrap">

function verifyBootstraps() {
  return
  !!localStorage.bootstrap &&
  !!localStorage.bootstrapVersion /*&&
  !!localStorage.html &&
  !!localStorage.htmlVersion &&
  !!localStorage.css &&
  !!localStorage.cssVersion*/;
}

if(!verifyBootstraps()) {
  initialSocket = new WebSocket("ws:"+document.location.hostname+":<?PHP echo SOCKET_PORT; ?>/socket.php");
  initialSocket.onopen = function (event) {
    initialSocket.send(JSON.stringify({type: <?PHP echo PACKET_TYPE_REQUEST_BOOTSTRAP; ?>, data: localStorage.bootstrapVersion}));
    // initialSocket.send(JSON.stringify({type: <?PHP echo PACKET_TYPE_REQUEST_HTML; ?>, data: localStorage.htmlVersion}));
    // initialSocket.send(JSON.stringify({type: <?PHP echo PACKET_TYPE_REQUEST_CSS; ?>, data: localStorage.cssVersion}));
  }
  initialSocket.tryClose = function () {
    if(verifyBootstraps()) {
      initialSocket.close();
      delete initialSocket;
    }
  }
  initialSocket.onmessage = function (event) {
    var data = JSON.parse(event.data).data;
    switch(JSON.parse(event.data).type) {
      case <?PHP echo PACKET_TYPE_BOOTSTRAP; ?>:
        eval(data);
        localStorage.bootstrap=data;
        break;
      case <?PHP echo PACKET_TYPE_HTML; ?>:
        localStorage.html=data;
        break;
      case <?PHP echo PACKET_TYPE_CSS; ?>:
      localStorage.css=data;
        break;
    }
    initialSocket.tryClose();
  }
} else {
  var script = document.createElement('script');
  script.innerHTML = localStorage.bootstrap;
  document.head.appendChild(script);
}
<?php
  /*

async function updateBootstrap() {
  const firstTime = localStorage.bootstrap=="";
  if(firstTime) {
    localStorage.bootstrapVersion = "null";
    localStorage.htmlVersion      = "null";
    localStorage.cssVersion       = "null";
  }

  const bootstrap = await fetch('/resource/bootstrap.php?type=bootstrap&version='+localStorage.bootstrapVersion, {method: 'post', mode: 'no-cors'}).then(r=>r.text());

  if(firstTime || bootstrap!="") {
    localStorage.bootstrap = bootstrap;
    if(firstTime) {
      eval(localStorage.bootstrap);
      console.warn("Welcome! We've detected it's your first time, and have just set everything up. To work the site needs a reload.")
      window.location.reload();
    }
    console.log("Script Update finished!, will be implemented on reload");
  }
}
// updateBootstrap();
*/
?>
</script>
