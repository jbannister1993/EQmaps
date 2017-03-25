<?php

header("Content-type: application/json");

$conn = new PDO("mysql:host=localhost;dbname=eqmap","eqmapsadmin","c#S{2;(]*s8^");
//$conn = new PDO("mysql:host=localhost;dbname=jbannister","jbannister","oPu0phah");

//Return an error if no data is returned in all fields
if ($_GET['earliest'] == "" && $_GET['latest'] == "" && $_GET['min_depth'] == "" && $_GET['max_depth'] == "" && $_GET['min_mag'] == "" && $_GET['max_mag'] == "" && $_GET['location'] == "")
{
	header("HTTP/1.1 400 Bad Request");
	die("400");
}

//Set earliest time to UNIX epoch if latest date entered but earliest date left blank
if ($_GET['earliest'] == "" && $_GET['latest'] != "")
{
	//$earliest = strtotime("1 January 2017") * 1000;
	header("HTTP/1.1 413 Payload Too Large");
	die("413");
}
else
{
	$early = $_GET['earliest'];
	$efixed = str_replace("T", " ", $early);
	$earliest = strtotime($efixed) * 1000;
}

//Set latest date to the current date and time if left blank but earliest date is entered
if ($_GET['earliest'] != "" && $_GET['latest'] == "")
{
	$latest = strtotime("now") * 1000;
}
else
{
	$late = $_GET['latest'];
	$lfixed = str_replace("T", " ", $late);
	$latest = strtotime($lfixed) * 1000;
}

//Set minimum depth to -9999 if only maximum depth is given
if ($_GET['min_depth'] == "" && $_GET['max_depth'] != "")
{
	$min_depth = -9999;
}
else
{
	$min_depth = $_GET['min_depth'];
}

//Set maximum depth to 9999 if only minimum depth is given
if ($_GET['min_depth'] != "" && $_GET['max_depth'] == "")
{
	$max_depth = 9999;
}
else
{
	$max_depth = $_GET['max_depth'];
}

//Set minimum magnitude to -99 if only maximum depth is given
if ($_GET['min_mag'] == "" && $_GET['max_mag'] != "")
{
	$min_mag = -99;
}
else
{
	$min_mag = $_GET['min_mag'];
}

//Set maximum magnitude to 99 if only minimum depth is given
if ($_GET['min_mag'] != "" && $_GET['max_mag'] == "")
{
	$max_mag = 99;
}
else
{
	$max_mag = $_GET['max_mag'];
}

//Returns an error if a value that is supposed to be the smallest is entered as the largest
if($earliest >= $latest || $min_depth >= $max_depth || $min_mag >= $max_mag)
{
	header("HTTP/1.1 406 Not Acceptable");
	die("406");
}

$location = $_GET['location'];

//Begin query construction...
$sql = "SELECT * FROM earthquakes WHERE";
$date = false;
$depth = false;
$mag = false;
$place = false;

if($earliest != "" && $latest != "")
{
	$sql .= " date >= :earliest AND date <= :latest AND";
	$date = true;
}

if($min_depth != "" && $max_depth != "")
{
	$sql .= " depth >= :min_depth AND depth <= :max_depth AND";
	$depth = true;
}

if($min_mag != "" && $max_mag != "")
{
	$sql .= " magnitude >= :min_mag AND magnitude <= :max_mag AND";
	$mag = true;
}

if($location != "")
{
	$sql .= " location LIKE CONCAT('%',:location,'%') AND";
	$place = true;
}

$qry = rtrim($sql,"A..Z");
$qry .= "ORDER BY date ASC;";

$search = $conn->prepare($qry);

if($date == true)
{
	$search->bindParam(":earliest",$earliest);
	$search->bindParam(":latest",$latest);
	//echo $earliest;
	//echo $latest;
}

if($depth == true)
{
	$search->bindParam(":min_depth",$min_depth);
	$search->bindParam(":max_depth",$max_depth);
	//echo $min_depth;
	//echo $max_depth;
}

if($mag == true)
{
	$search->bindParam(":min_mag",$min_mag);
	$search->bindParam(":max_mag",$max_mag);
	//echo $min_mag;
	//echo $max_mag;
}

if($place == true)
{
	$search->bindParam(":location",$location);
	//echo $location;
}

//print_r($search);

//Execute search
$search->execute();

$row = $search->fetch(PDO::FETCH_ASSOC);

//If no results found...
if ($row == false)
{
	header("HTTP/1.1 404 Not Found");
	die("404");
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
	//print_r($results);
}

?>