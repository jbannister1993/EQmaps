<?php

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
	$password = password_hash($pass, PASSWORD_BCRYPT);
}

//if(!isset($_POST["user"]) || !isset($_POST["pass"]))
//{
//	header("HTTP/1.1 400 Bad Request");
//	die("400");
//}
//else
//{
//	$user = $_POST["user"];
//	$pass = $_POST["pass"];
//	$password = password_hash($pass, PASSWORD_BCRYPT);
//}

if(strlen($user) > 16 || strlen($user) < 4)
{
	header("HTTP/1.1 412 Precondition Failed");
	die("412");
}
else if (!ctype_alnum($user))
{
	header("HTTP/1.1 406 Not Acceptable");
	die("406");
}
else
{
	$checkexists = $conn->prepare("SELECT * FROM users WHERE username=?");
	$checkexists->bindParam(1,$user);
	$checkexists->execute();
	$row = $checkexists->fetch();
	
	if($row == true)
	{
		header("HTTP/1.1 409 Conflict");
		die("409");
	}
	else
	{
		$register = $conn->prepare("INSERT INTO users (username,password) VALUES (?,?)");
		$register->bindParam(1,$user);
		$register->bindParam(2,$password);
		$register->execute();
		
		header("HTTP/1.1 200 OK");
	}
}

?>