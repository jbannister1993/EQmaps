<?php

session_start();
header("Content-type: application/json");

$conn = new PDO("mysql:host=localhost;dbname=eqmap","eqmapsadmin","c#S{2;(]*s8^");
//$conn = new PDO("mysql:host=localhost;dbname=jbannister","jbannister","oPu0phah");

if(!isset($_SESSION["guardian_id"]))
{
	header("HTTP/1.1 401 Unauthorised");
	die("401");
}
else
{
	$id = $_SESSION["guardian_id"];
}

$checkbook = $conn->prepare("SELECT * FROM bookmarks, earthquakes WHERE quake_id=id AND user_id=?");
$checkbook->bindParam(1,$id);
$checkbook->execute();
$bookrow = $checkbook->fetch();

if($bookrow == false)
{
	header("HTTP/1.1 417 Expectation Failed");
	die("417");
}
else
{
	$bookmarks = array();
    while($bookrow)
    {
        $bookmarks[] = $bookrow;
        $bookrow = $checkbook->fetch(PDO::FETCH_ASSOC);
    }
            
    header("HTTP/1.1 200 OK");

    echo json_encode($bookmarks);	
}

?>