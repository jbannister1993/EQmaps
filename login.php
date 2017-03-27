<?php

session_start();

$conn = new PDO("mysql:host=localhost;dbname=eqmap","eqmapsadmin","c#S{2;(]*s8^");
//$conn = new PDO("mysql:host=localhost;dbname=jbannister","jbannister","oPu0phah");

if(!isset($_SERVER["PHP_AUTH_USER"]) || !isset($_SERVER["PHP_AUTH_PW"]))
{
	header("HTTP/1.1 400 Bad Request");
	die("400");
}
else
{
	$user = $_SERVER["PHP_AUTH_USER"];
	$pass = $_SERVER["PHP_AUTH_PW"];
}

$checkuser = $conn->prepare("SELECT * FROM users WHERE username=?");
$checkuser->bindParam(1,$user);
$checkuser->execute();
$row = $checkuser->fetch();

if($row == false)
{
	header("HTTP/1.1 404 Not Found");
	die("404");	
}
else if(password_verify($pass, $row["password"]))
{
	$_SESSION["guardian"] = $row["id"];
	header("HTTP/1.1 200 OK");
}
else
{
	header("HTTP/1.1 401 Unauthorized");
	die("401");
}

?>