<?php
require $_SERVER['DOCUMENT_ROOT']."/../htresources/config.php";
// @session_start();

// Create connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);

// Check connection
if ($conn->connect_error) {
    error_log("SQL Connection failed: " . $conn->connect_error);
    die();
}

//Check database
$db = $conn->select_db(DB_DATABASE);
  if(!$db) {
  // Create database
  if ($conn->query("CREATE DATABASE `".DB_DATABASE."`") === TRUE) {
    error_log("Database `".DB_DATABASE."` did not exist, created it successfully");
    $db = $conn->select_db(DB_DATABASE);
  } else {
    error_log("Error creating database `".DB_DATABASE."`: " . $conn->error);
  }
}

//Check table
if(!($conn->query("SELECT ID, USERNAME, PASSWORD, JOINED, ACCESS_LEVEL, LAST_SEEN FROM `".DB_TABLE_USERS."`"))) {
  $create_query = "CREATE TABLE `".DB_TABLE_USERS."` (
                    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                    `username` VARCHAR(50) NOT NULL UNIQUE,
                    `password` VARCHAR(255) NOT NULL,
                    `joined` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `access_level` TINYINT(1) NOT NULL DEFAULT '1',
                    `last_seen` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                  )";
  // Create table
  if ($conn->query($create_query) === TRUE) {
    error_log("Table `".DB_TABLE_USERS."` did not exist, created it successfully");
  } else {
    error_log("Error creating table `".DB_TABLE_USERS."`: " . $conn->error);
  }
}

if(!$db) {
  error_log("Connection error!");
  die();
}

function login($username, $password) {
  echo $username."\n".$password."\n";
  $username = htmlspecialchars($username);
  $username = $conn->real_escape_string($username);

  $password = htmlspecialchars($password);
  $password = $conn->real_escape_string($password);

  //Check that result is good
  if (!($result = $mysqli->query($query))) {
    error_log("result error: " . $conn->error);
    return false;
  }

  //Check there is only 1 result
  if($result->num_rows()!=1) {
    error_log("result num_rows !=1: " . $conn->error);
    return false;
  }

  //TESTING print everyhting
  while ($row = $result->fetch_assoc()) {
      printf ("\n\n%s\n\n", $row);
  }

  //Free result set
  $result->free();



  $result = $conn->query("SELECT id FROM admin WHERE username = `$username` and password = `$password`");
  $row = $conn->fetch();
  $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
  $active = $row['active'];

  $count = mysqli_num_rows($result);

  // If result matched $myusername and $mypassword, table row must be 1 row

  if($count == 1) {
     // session_register("myusername");
     // $_SESSION['login_user'] = $myusername;

     return true;
    // header("location: welcome.php");
  } else {
    return false;
    // die("Your Login Name or Password is invalid");
  }
  return false;
  // die("Your Login Name or Password is invalid");
}
?>
