<?php

header("Content-type: application/json");

$conn = new PDO("mysql:host=localhost;dbname=eqmap","eqmapsadmin","c#S{2;(]*s8^");
//$conn = new PDO("mysql:host=localhost;dbname=jbannister","jbannister","oPu0phah");

//Return an error if no data is returned in all fields
if (!isset($_GET['earliest']) || $_GET['earliest'] == "" && !isset($_GET['latest']) || $_GET['latest'] == "" && !isset($_GET['min_depth']) || $_GET['min_depth'] == "" && !isset($_GET['max_depth']) || $_GET['max_depth'] == "" && !isset($_GET['min_mag']) || $_GET['min_mag'] == "" && !isset($_GET['max_mag']) || $_GET['max_mag'] == "" && !isset($_GET['location']) || $_GET['location'] == "")
{
	header("HTTP/1.1 400 Bad Request");
}

//Set earliest time to UNIX epoch if latest date entered but earliest date left blank
if (!isset($_GET['earliest']) || $_GET['earliest'] == "" && isset($_GET['latest']) || $_GET['latest'] != "")
{
	$earliest = strtotime("1 January 1970") * 1000;
}
else
{
	$early = $_GET['earliest'];
	$earliest = strtotime($early) * 1000;
}

//Set latest date to the current date and time if left blank but earliest date is entered
if (isset($_GET['earliest']) || $_GET['earliest'] != "" && !isset($_GET['latest']) || $_GET['latest'] == "")
{
	$latest = strtotime("now") * 1000;
}
else
{
	$late = $_GET['latest'];
	$latest = strtotime($late) * 1000;
}

//Set minimum depth to -9999 if only maximum depth is given
if (!isset($_GET['min_depth']) || $_GET['min_depth'] == "" && isset($_GET['max_depth']) || $_GET['max_depth'] != "")
{
	$min_depth = -9999;
}
else
{
	$min_depth = $_GET['min_depth'];
}

//Set maximum depth to 9999 if only minimum depth is given
if (isset($_GET['min_depth']) || $_GET['min_depth'] != "" && !isset($_GET['max_depth']) || $_GET['max_depth'] == "")
{
	$max_depth = 9999;
}
else
{
	$max_depth = $_GET['max_depth'];
}

//Set minimum magnitude to -99 if only maximum depth is given
if (!isset($_GET['min_mag']) || $_GET['min_mag'] == "" && isset($_GET['max_mag']) || $_GET['max_mag'] != "")
{
	$min_mag = -99;
}
else
{
	$min_mag = $_GET['min_mag'];
}

//Set maximum magnitude to 99 if only minimum depth is given
if (isset($_GET['min_mag']) || $_GET['min_mag'] != "" && !isset($_GET['max_mag']) || $_GET['max_mag'] == "")
{
	$max_mag = 99;
}
else
{
	$max_mag = $_GET['max_mag'];
}

//Returns an error if a value that is supposed to be the smallest is entered as the largest
if($early >= $late || $min_depth >= $max_depth || $min_mag >= $max_mag)
{
	header("HTTP/1.1 406 Not Acceptable");
}

$location = $_GET['location'];

//Begin query construction...
$sql = "SELECT * FROM earthquakes WHERE";
$date = false
$depth = false
$mag = false
$place = false

if(isset($_GET['earliest']) || isset($_GET['latest']))
{
	$sql += " date >= :earliest AND date <= :latest AND";
	$date = true;
}

if(isset($_GET['min_depth']) || isset($_GET['max_depth']))
{
	$sql += " depth >= :min_depth AND depth <= :max_depth AND";
	$depth = true;
}

if(isset($_GET['min_mag']) || isset($_GET['max_mag']))
{
	$sql += " magnitude >= :min_mag AND magnitude <= :max_mag AND";
	$mag = true;
}

if(isset($_GET['location']))
{
	$sql += " location LIKE CONCAT('%',':location','%') AND";
	$place = true;
}

$query = substr($sql,0,-3);

$search = $conn->prepare($query);

if($date == true)
{
	$search->bindParam(:earliest,$earliest);
	$search->bindParam(:latest,$latest);
}

if($depth == true)
{
	$search->bindParam(:min_depth,$min_depth);
	$search->bindParam(:max_depth,$max_depth);
}

if($mag == true)
{
	$search->bindParam(:min_mag,$min_mag);
	$search->bindParam(:max_mag,$max_mag);
}

if($place == true)
{
	$search->bindParam(:location,$location);
}

//Execute search
$search->execute();

$row = $search->fetch(PDO::FETCH_ASSOC);

//If no results found...
if ($row == false)
{
	header("HTTP/1.1 404 Not Found");
}
//Send back result
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

?>