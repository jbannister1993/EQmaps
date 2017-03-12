<?php

header("Content-type: application/json");

$conn = new PDO("mysql:host=localhost;dbname=eqmap","eqmapsadmin","c#S{2;(]*s8^");
//$conn = new PDO("mysql:host=localhost;dbname=jbannister","jbannister","oPu0phah");

// Convert current time to seconds then multiply by 1000 to get it in milleseconds
$latest = strtotime("now") * 1000;
// Calculate 24 hours prior to now (86400000 = 24 hours in milleseconds)
$earliest = $latest - 86400000;

$search = $conn->prepare("SELECT * FROM earthquakes WHERE date >= ? AND date <= ?");
$search->bindParam(1,$earliest);
$search->bindParam(2,$latest);
$search->execute();

$row = $search->fetch(PDO::FETCH_ASSOC);

if ($row == false)
{
    header("HTTP/1.1 404 Not Found");
}
else
{
    $results = array();
    while($row)
    {
        $results[] = $row;
        $row = $search->fetch(PDO::FETCH_ASSOC);
    }
            
    header("HTTP/1.1 200 OK");

    echo json_encode($results);	
}