<?php

session_start();

$conn = new PDO("mysql:host=localhost;dbname=eqmap","eqmapsadmin","c#S{2;(]*s8^");
//$conn = new PDO("mysql:host=localhost;dbname=jbannister","jbannister","oPu0phah");

if(!isset($_SESSION["guardian"]))
{
	header("HTTP/1.1 401 Unauthorised");
	die("401");
}
else
{
	$uid = $_SESSION["guardian"];
	
	$deletebooked = $conn->prepare("DELETE FROM bookmarks WHERE user_id=?");
	$deletebooked->bindParam(1,$uid);
	$deletebooked->execute();
	
	$deleteaccount = $conn->prepare("DELETE FROM users WHERE id=?");
	$deleteaccount->bindParam(1,$uid);
	$deleteaccount->execute();
	
	session_destroy();
	
	header("HTTP/1.1 200 OK");
}

?>