<?php

session_start();

$conn = new PDO("mysql:host=localhost;dbname=eqmap","eqmapsadmin","c#S{2;(]*s8^");
//$conn = new PDO("mysql:host=localhost;dbname=jbannister","jbannister","oPu0phah");

//Workaround for FastCGI php
list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':' , base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));

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

//if(!isset($_GET["user"]) || !isset($_GET["pass"]))
//{
//	header("HTTP/1.1 400 Bad Request");
//	die("400");
//}
//else
//{
//	$user = $_GET["user"];
//	$pass = $_GET["pass"];
//}

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