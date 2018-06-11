<?php
date_default_timezone_set('Australia/Sydney');

if (!defined("BASE_PATH")) define('BASE_PATH', isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : substr($_SERVER['PATH_TRANSLATED'],0, -1*strlen($_SERVER['SCRIPT_NAME'])));

@define('DEBUG_MODE', true);

@define('SOCKET_IP', '127.0.0.1');
@define('SOCKET_PORT', '8080');
@define('SOCKET_DEBUG_IP', '127.0.0.1');
@define('SOCKET_DEBUG_PORT', '8080');

$packetId=0;
@define('PACKET_TYPE_ERROR', $packetId++);
@define('PACKET_TYPE_REQUEST_BOOTSTRAP', $packetId++);
@define('PACKET_TYPE_REQUEST_HTML', $packetId++);
@define('PACKET_TYPE_REQUEST_CSS', $packetId++);
@define('PACKET_TYPE_BOOTSTRAP', $packetId++);
@define('PACKET_TYPE_HTML', $packetId++);
@define('PACKET_TYPE_CSS', $packetId++);
@define('PACKET_TYPE_SIGNUP', $packetId++);
@define('PACKET_TYPE_LOGIN', $packetId++);
@define('PACKET_TYPE_NORMAL', $packetId++);

@define('ERROR_PACKET_DO_LOGIN', 0);
@define('ERROR_PACKET_INVALID_LOGIN', 1);

@define('SOCKET_RECONNECT_TIMEOUT', 5*1000);
@define('BOOTSTRAP_UPDATE_TIMEOUT', 60*1000);

@define('DB_SERVER', 'localhost');
@define('DB_USERNAME', 'root');
@define('DB_PASSWORD', 'root');
@define('DB_DATABASE', 'database');
@define('DB_TABLE_USERS', 'users');

?>
