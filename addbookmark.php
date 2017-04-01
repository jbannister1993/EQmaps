<?php

session_start();

$conn = new PDO("mysql:host=localhost;dbname=eqmap","eqmapsadmin","c#S{2;(]*s8^");
//$conn = new PDO("mysql:host=localhost;dbname=jbannister","jbannister","oPu0phah");

if(!isset($_POST["eid"]))
{
	header("HTTP/1.1 400 Bad Request");
	die("400");
}
else if(!isset($_SESSION["guardian_id"]))
{
	header("HTTP/1.1 401 Unauthorized");
	die("401");
}
else
{
	$uid = $_SESSION["guardian_id"];
	$eid = $_POST["eid"];
}

$checkexists = $conn->prepare("SELECT * FROM earthquakes WHERE id=?");
$checkexists->bindParam(1,$eid);
$checkexists->execute();
$existrow = $checkexists->fetch();

if($existrow == false)
{
	header("HTTP/1.1 404 Not Found");
	die("404");
}
else
{
	$checkconflict = $conn->prepare("SELECT * FROM bookmarks WHERE user_id=? AND quake_id=?");
	$checkconflict->bindParam(1,$uid);
	$checkconflict->bindParam(2,$eid);
	$checkconflict->execute();
	$conrow = $checkconflict->fetch();
	
	if($conrow == true)
	{
		header("HTTP/1.1 409 Conflict");
		die("409");
	}
	else
	{
		$addbookmark = $conn->prepare("INSERT INTO bookmarks (user_id,quake_id) VALUES (?,?)");
		$addbookmark->bindParam(1,$uid);
		$addbookmark->bindParam(2,$eid);
		$addbookmark->execute();
		
		header("HTTP/1.1 200 OK");
	}
}

?>