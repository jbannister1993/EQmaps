<?php

$conn = new PDO("mysql:host=localhost;dbname=eqmap","eqmapsadmin","c#S{2;(]*s8^");
//$conn = new PDO("mysql:host=localhost;dbname=jbannister","jbannister","oPu0phah");

if(!isset($_POST["eid"]))
{
	header("HTTP/1.1 400 Bad Request");
	die("400");
}
else if(!isset($_POST["uid"]))
{
	header("HTTP/1.1 401 Unauthorized");
	die("401");
}
else
{
	$uid = $_POST["uid"];
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
	$checkbooked = $conn->prepare("SELECT * FROM bookmarks WHERE user_id=? AND quake_id=?");
	$checkbooked->bindParam(1,$uid);
	$checkbooked->bindParam(2,$eid);
	$checkbooked->execute();
	$bookedrow = $checkbooked->fetch();
	
	if($bookedrow == false)
	{
		header("HTTP/1.1 417 Expectation Failed");
		die("417");
	}
	else
	{
		$removebookmark = $conn->prepare("DELETE FROM bookmarks WHERE user_id=? AND quake_id=?");
		$removebookmark->bindParam(1,$uid);
		$removebookmark->bindParam(2,$eid);
		$removebookmark->execute();
		
		header("HTTP/1.1 200 OK");
	}
}

?>