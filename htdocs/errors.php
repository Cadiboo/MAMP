<?php
require $_SERVER['DOCUMENT_ROOT']."/../htresources/config.php";
if(!empty($_REQUEST['error'])) {
	error_log("REPORTED ERROR: ".$_REQUEST['error']);
	// die("error recieved & logged");
	die();
}
?>
