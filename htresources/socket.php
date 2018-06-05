<?php
if(empty($_SERVER['SHELL'])) {
  die("Socket unavailable with direct connection, I'll put a pure HTTP version maybe probably not\n");
}

// prevent the server from timing out
set_time_limit(0);

if(!isset($_SERVER['DOCUMENT_ROOT']) || empty($_SERVER['DOCUMENT_ROOT']))
  $_SERVER['DOCUMENT_ROOT'] = explode("htdocs", $_SERVER['PHP_SELF'])[0]."htdocs";

require $_SERVER['DOCUMENT_ROOT']."/resource/login.php";
// include the web sockets server script (the server is started at the far bottom of this file)
require $_SERVER['DOCUMENT_ROOT']."/resource/class.PHPWebSocket.php";

function createPacket($type, $message) {
  $json = new StdClass();
  $json->type = $type;
  $json->data = $message;
  return json_encode($json);
}

//TESTING DONT FUCKING USE THIS FOR THE ACTUAL ENCRYPTION
function encrypt(&$message) {
  // $message = urlencode(base64_encode($message));
  return $message;
}

function decrypt(&$message) {
  // $message = base64_decode(urldecode($message));
  return $message;
}

function verify(&$Server, &$clientID) {
  return;
  $session = $Server->wsClients[$clientID][PHPWebSocket::SESSION];
  if(empty($session) || !login($session['username'], $session['password'])) {
    $Server->wsSend($clientID, createPacket(PACKET_TYPE_ERROR, empty($session)?ERROR_PACKET_DO_LOGIN:ERROR_PACKET_INVALID_LOGIN));

    //Load & destroy session
    @session_start($Server->getSessionID($clientID));
    @session_destroy();
    //Clean up everything
    $Server->loadSession($clientID);

    if ($Server->wsClients[$clientID][PHPWebSocket::READY_STATE] != PHPWebSocket::WS_READY_STATE_CONNECTING) {
      $Server->wsSendClientClose($clientID, PHPWebSocket::WS_STATUS_NORMAL_CLOSE);
    }
    $Server->wsRemoveClient($clientID);
    return false;
  }
  return true;
}

// when a client sends data to the server
function wsOnMessage($clientID, $message, $messageLength, $binary) {
	global $Server;

  verify($Server, $clientID);

	if ($messageLength == 0) {
		return;
	}

  decrypt($message);

  $packet = json_decode($message);
  if(!$packet) {
    $Server->wsSend($clientID, createPacket(PACKET_TYPE_ERROR, "Invalid packet: ".$message));
    return;
  }
  switch($packet->type) {
    case PACKET_TYPE_ERROR:
      $Server->wsSend($clientID, createPacket(PACKET_TYPE_NORMAL, "Recieved Error packet: ".$message));
      break;
    case PACKET_TYPE_SIGNUP:
      $Server->wsSend($clientID, createPacket(PACKET_TYPE_NORMAL, "Recieved Signup packet: ".$message));
      break;
    case PACKET_TYPE_LOGIN:
      $Server->wsSend($clientID, createPacket(PACKET_TYPE_NORMAL, "Recieved Login packet: ".$message));
      break;
    case PACKET_TYPE_NORMAL:
      $Server->wsSend($clientID, createPacket(PACKET_TYPE_NORMAL, "Recieved Normal packet: ".$message));
      break;
    default:
      $Server->wsSend($clientID, createPacket(PACKET_TYPE_ERROR, "Recieved Unknown packet: ".$message));
    break;
  }
		// foreach ( $Server->wsClients as $id => $client )
		// 	if ( $id != $clientID )
    //
	// $Server->wsSend($clientID, createPacket(PACKET_TYPE_NORMAL, "Recieved: ".$message));
}

// when a client connects
function wsOnOpen($clientID)
{
	global $Server;

	$ip = long2ip( $Server->wsClients[$clientID][PHPWebSocket::IPv4] );

	$Server->log( "$ip ($clientID) has connected." );
  //Verify the client
  verify($Server, $clientID);

	//Send a join notice to everyone but the person who joined
	// foreach ( $Server->wsClients as $id => $client )
	// 	if ( $id != $clientID )
	// 		$Server->wsSend($id, "Visitor $clientID ($ip) has joined the room.");
}

// when a client closes or lost connection
function wsOnClose($clientID, $status) {
	global $Server;
	$ip = long2ip( $Server->wsClients[$clientID][PHPWebSocket::IPv4] );

	$Server->log( "$ip ($clientID) has disconnected." );

	//Send a user left notice to everyone in the room
	// foreach ( $Server->wsClients as $id => $client )
}

function wsOnSend($clientID, &$message, $binary) {
  encrypt($message);
}

// start the server
$Server = new PHPWebSocket();
$Server->log("Initialising Server...");
$Server->bind('message', 'wsOnMessage');
$Server->bind('open', 'wsOnOpen');
$Server->bind('close', 'wsOnClose');
$Server->bind('send', 'wsOnSend');

$debug = isset(getopt("", array("debug::"))['debug']);
$Server->log("Starting Server".($debug?" with Debugging Mode on":""));

// for other computers to connect, you will probably need to change this to your LAN IP or external IP,
// alternatively use: gethostbyaddr(gethostbyname($_SERVER['SERVER_NAME']))
$Server->wsStartServer($debug?SOCKET_DEBUG_IP:SOCKET_IP, $debug?SOCKET_DEBUG_PORT:SOCKET_PORT);

echo date('[Y-m-d H:i:s] ')."Server Closed\n";
?>
