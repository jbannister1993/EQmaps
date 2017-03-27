<?php

session_start();

if(isset($_SESSION["guardian"]))
{
	$id = $_SESSION["guardian"];
	$header = """<input type='submit' value='Bookmarks' onClick='bookmarks()'>
				<input type='submit' value='My Account' onClick='accountDialog()'>
				<input type='submit' value='Logout' onClick='<?php logout() ?>'>
				<input type='hidden' value='$id' id='user_id'>""";
}
else
{
	$header = """<input type='submit' value='Login' onClick='loginDialog()'>
				<input type='submit' value='Register' onClick='registerDialog()'>""";
}

function logout()
{
	session_destroy();
	header("Refresh:0");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	
	<title>EQmaps</title>
	
	<link rel="stylesheet" type="text/css" href="/leaflet/leaflet.css" />
	<link rel="stylesheet" type="text/css" href="/jqueryui/jquery-ui.css" />
	
	<style>
		#map	{
			z-index: 1;
			width: 1600px;
			height: 800px;
		}
		
		.form_desc	{
			font-style: italic;
		}
	</style>
	
	<script type="text/javascript" src="/leaflet/leaflet.js"></script>
	<script type="text/javascript" src="/jqueryui/external/jquery/jquery.js"></script>
	<script type="text/javascript" src="/jqueryui/jquery-ui.min.js"></script>
	
	<script type="text/javascript">
		var map;
		var markers = L.layerGroup();
		var bookmark = false;
		
		function startup()
		{
			map = L.map ("map");
			var attrib = "Map data copyright <a href='http://openstreetmap.org'>OpenStreetMap</a> contributors, Open Database License";	
			var eqmap = new L.tileLayer ("http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", { attribution: attrib } );
			map.setView(new L.LatLng(40,0), 2);
			map.addLayer(eqmap);
			
			recentEarthquakeSearch();
		}
		
		function recentEarthquakeSearch()
		{
			bookmark = false;
			var heading = "<h1>Recent Earthquakes</h1>";
			document.getElementById("heading").innerHTML = heading;
			var xhr2 = new XMLHttpRequest();
			xhr2.addEventListener ("load", populateList);
			xhr2.open("GET", "/web_services/recent.php");
			xhr2.send();
		}
		
		function userSearch()
		{
			$('#search').dialog('close');
			bookmark = false;
			var heading = "<h1>Search Results</h1>";
			document.getElementById("heading").innerHTML = heading;
			var xhr2 = new XMLHttpRequest();
			xhr2.addEventListener ("load", populateList);
			var earliest = document.getElementById("earliest").value;
			var latest = document.getElementById("latest").value;
			var min_depth = document.getElementById("min_depth").value;
			var max_depth = document.getElementById("max_depth").value;
			var min_mag = document.getElementById("min_mag").value;
			var max_mag = document.getElementById("max_mag").value;
			var location = document.getElementById("location").value;
			xhr2.open("GET", "/web_services/search.php?earliest=" + earliest + "&latest=" + latest + "&min_depth=" + min_depth + "&max_depth=" + max_depth + "&min_mag=" + min_mag + "&max_mag=" + max_mag + "&location=" + location);
			xhr2.send();
		}
		
		function populateList(e)
		{
			markers.clearLayers();
			var status = e.target.status;	
					
			if(status == 200)
			{
				var data = JSON.parse(e.target.responseText);
				var list = "<hr />";
				
				if(bookmark == true)
				{
					var book = "<a href = 'removeBookmark(" + data[i].id + ")'>Remove</a>";
				}
				else
				{
					var book = "<a href = 'addBookmark(" + data[i].id + ")'>Bookmark</a>";
				}
				
				for(var i=0; i<data.length; i++)
				{
					var location = data[i].location;
					var lat = "Lat: " + data[i].latitude;
					var lon = "Lon: " + data[i].longitude;
					var mag = "Mag: " + data[i].magnitude + " md";
					var depth = "Depth: " + data[i].depth + " km";
					var url = "<a href = '" + data[i].url + "'>More Info</a>";
					var int = parseInt(data[i].date);
					var time = new Date(int);
					//var dateTime = time.toISOString();
					var year = time.getFullYear();
					var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
					var month = months[time.getMonth()];
					//var month = "0" + time.getMonth();
					var date = "0" + time.getDate();
					var hour = "0" + time.getHours();
					var minute = "0" + time.getMinutes();
					var second = "0" + time.getSeconds();
					var dateTime = date.substr(-2) + "-" + month + "-" + year + " at " + hour.substr(-2) + ":" + minute.substr(-2) + ":" + second.substr(-2) + " (UTC)";
					marker = L.marker([data[i].latitude, data[i].longitude]);
					marker.bindPopup("<table><tr><td colspan = '4'>" + location + "</td></tr><tr><td colspan = '4'>" + dateTime + "</td></tr><tr><td>" + lat + "</td><td>" + lon + "</td><td>" + mag + "</td><td>" + depth + "</td></tr><tr><td colspan = '2'>" + book + "</td><td colspan = '2'>" + url + "</td></tr></table>");
					list += "<table><tr><td>" + location + "</td><td>" + lat + "</td><td>" + mag + "</td><td>" + book + "</td></tr><tr><td>" + dateTime + "</td><td>" + lon + "</td><td>" + depth + "</td><td>" + url + "</td></tr></table><hr />";
					markers.addLayer(marker);
				}
				
				map.addLayer(markers);
				document.getElementById('list').innerHTML = list;
			}
			else if(status == 400)
			{
				alert("You must fill in at least one search term");
			}
			else if(status == 404)
			{
				alert("No results found. Please try new search criteria or try again later.");
			}
			else if(status == 406)
			{
				alert("Your search was invalid. Please try a different search.")
			}
			else if(status == 413)
			{
				alert("An earliest date must be entered to prevent server overload!");
			}
			else
			{
				alert("An unknown error has occurred. Please try again later.");
			}
		}
		
		function searchDialog()
		{
			$('#search').dialog( {
							title: "Search for Earthquakes",
							modal: true,
							resizable: false,
							width: 800,
							height: 600,
							buttons:
								{	'Search'	:	userSearch,
									'Cancel'	:	function() {	$(this).dialog('close')	}	}
						} );
		}
		
		function registerDialog()
		{
			$('#reglog').dialog( {
							title: "Register for EQmaps",
							modal: true,
							resizable: false,
							width: 400,
							height: 400,
							buttons:
								{	'Register'	:	register,
									'Cancel'	:	function() {	$(this).dialog('close')	}	}
						} );
		}
		
		function loginDialog()
		{
			$('#reglog').dialog( {
							title: "Login to EQmaps",
							modal: true,
							resizable: false,
							width: 400,
							height: 400,
							buttons:
								{	'Login'		:	login,
									'Cancel'	:	function() {	$(this).dialog('close')	}	}
						} );
		}
		
		function register()
		{
			$('#reglog').dialog('close');
			var xhr2 = new XMLHttpRequest();
			xhr2.addEventListener ("load", registered);
			var user = document.getElementById("username").value;
			var pass = document.getElementById("password").value;
			xhr2.open("POST", "register.php");
			xhr2.setRequestHeader("Authorization", "Basic " + btoa(user + ":" + pass));
			xhr2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhr2.send();
		}
		
		function registered(e)
		{
			var status = e.target.status;
			
			if(status == 200)
			{
				alert("Registered successfully! You may now login.")
			}
			else if(status == 400)
			{
				alert("You must enter both a username and a password!");
			}
			else if(status == 406)
			{
				alert("Username cannot contain special characters!");
			}
			else if(status == 409)
			{
				alert("Username already exists! Sorry! Please try again.");
			}
			else if(status == 412)
			{
				alert("Username/Password either too long or too short!");
			}
			else
			{
				alert("An unknown error has occurred. Please try again later.");
			}
		}
		
		function login()
		{
			$('#reglog').dialog('close');
			var xhr2 = new XMLHttpRequest();
			xhr2.addEventListener ("load", loggedin);
			var user = document.getElementById("username").value;
			var pass = document.getElementById("password").value;
			xhr2.open("GET", "login.php");
			xhr2.setRequestHeader("Authorization", "Basic " + btoa(user + ":" + pass));
			xhr2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhr2.send();
		}
		
		function loggedin(e)
		{
			var status = e.target.status;
			
			if(status == 200)
			{
				location.reload(true);
			}
			else if(status == 400)
			{
				alert("You must enter both a username and a password!");
			}
			else if(status == 401)
			{
				alert("Username/Password combination incorrect!");
			}
			else if(status == 404)
			{
				alert("Account does not exist!");
			}
			else
			{
				alert("An unknown error has occurred. Please try again later.");
			}
		}
		
		function bookmarks()
		{
			bookmark = true;
			var heading = "<h1>Bookmarked Earthquakes</h1>";
			document.getElementById("heading").innerHTML = heading;
			var id = document.getElementById("user_id").value;
			var xhr2 = new XMLHttpRequest();
			xhr2.addEventListener ("load", populateList);
			xhr2.open("GET", "bookmarks.php?id=" + id);
			xhr2.send();
		}
		
		function accountDialog()
		{		
			$('#account').dialog( {
							title: "Account Settings",
							modal: true,
							resizable: false,
							width: 400,
							height: 400,
							buttons:
								{	'Delete Account'		:	deleteDialog,
									'Cancel'				:	function() {	$(this).dialog('close')	}	}
						} );
		}
		
		function addBookmark(eid)
		{
			var uid = document.getElementById("user_id").value;
			var xhr2 = new XMLHttpRequest();
			xhr2.addEventListener ("load", bookmarkAdded);
			xhr2.open("POST", "addbookmark.php?uid=" + uid + "&eid=" + eid);
			xhr2.send();
		}
		
		function bookmarkAdded(e)
		{
			var status = e.target.status;
			
			if(status == 200)
			{
				alert("Bookmark successfully added!");
			}
			else if(status == 400 || status == 404)
			{
				alert("Earthquake does not exist!");
			}
			else if(status == 401)
			{
				alert("You must be logged in to bookmark an earthquake!");
			}
			else if(status == 409)
			{
				alert("You have already bookmarked this earthquake!");
			}
			else
			{
				alert("An unknown error has occurred. Please try again later.");
			}
		}
		
		function removeBookmark(eid)
		{
			var uid = document.getElementById("user_id").value;
			var xhr2 = new XMLHttpRequest();
			xhr2.addEventListener ("load", bookmarkRemoved);
			xhr2.open("POST", "removebookmark.php?uid=" + uid + "&eid=" + eid);
			xhr2.send();
		}
		
		function bookmarkRemoved(e)
		{
			var status = e.target.status;
			
			if(status == 200)
			{
				alert("Bookmark successfully removed!");
			}
			else if(status == 400 || status == 404)
			{
				alert("Earthquake does not exist!");
			}
			else if(status == 401)
			{
				alert("You must be logged in to remove a bookmark!");
			}
			else if(status == 410)
			{
				alert("This earthquake is not bookmarked!");
			}
			else
			{
				alert("An unknown error has occurred. Please try again later.");
			}
		}
		
		function deleteDialog()
		{		
			$('#account').dialog('close');
			$('#delete').dialog( {
							title: "Delete Account",
							modal: true,
							resizable: false,
							width: 400,
							height: 400,
							buttons:
								{	'Confirm'				:	deleteAccount,
									'Cancel'				:	function() {	$(this).dialog('close')	}	}
						} );
		}
		
		function deleteAccount()
		{
			$('#delete').dialog('close');
			var id = document.getElementById("user_id").value;
			var xhr2 = new XMLHttpRequest();
			xhr2.addEventListener ("load", accountDeleted);
			xhr2.open("POST", "deleteaccount.php?id=" + id);
			xhr2.send();
		}
		
		function accountDeleted(e)
		{
			var status = e.target.status;
			
			if(status == 200)
			{
				location.reload(true);
			}
			else if(status == 400 || status == 404)
			{
				alert("Account does not exist!");
			}
			else
			{
				alert("An unknown error has occurred. Please try again later.");
			}
		}

	</script>
</head>
<body onload="startup()">
	<header>
		<h1>Welcome to EQmaps</h1>
		<?php echo $header ?>
	</header>
	
	<div id="map"></div>
	
	<div id="buttons">
		<input type='submit' value='Search' onClick='searchDialog()' />
		<input type='submit' value='Recent' onClick='recentEarthquakes()' />
	</div>
	
	<div id="heading"></div>
	<div id="list"></div>
	
	<div id="search" style="display: none;">
		<fieldset name="Date">
			<legend>Date</legend>
			<p class="form_desc">Enter a minimum and maximum date for your search. The minimum date is required whilst omission of the latter will results in today's date and time being used.</p><br />
			<label for "earliest">Between:</label><input type="datetime-local" name="earliest" id="earliest" required>
			<label for "latest">And:</label><input type="datetime-local" name="latest" id="latest">
		</fieldset>
		<fieldset name="Depth">
			<legend>Depth</legend>
			<p class="form_desc">Enter a minimum and maximum depth for your search. Neither are required but omission of the second value will search for all earthquakes deeper than the given depth and vice-versa.</p><br />
			<label for "min_depth">Between:</label><input type="number" name="min_depth" id="min_depth">
			<label for "max_depth">And:</label><input type="number" name="max_depth" id="max_depth">
		</fieldset>
		<fieldset name="Magnitude">
			<legend>Magnitude</legend>
			<p class="form_desc">Enter a minimum and maximum magnitude for your search. Neither are required but omission of the second value will search for all earthquakes of a greater magnitude than the given value and vice-versa.</p><br />
			<label for "min_mag">Between:</label><input type="number" name="min_mag" id="min_mag">
			<label for "max_mag">And:</label><input type="number" name="max_mag" id="max_mag">
		</fieldset>
		<fieldset name="Location">
			<legend>Location</legend>
			<p class="form_desc">Enter a search term if required. This can be either a country, a town, or an ocean name. If searching for within the United States, the state name can be used.</p><br />
			<label for "location">Location:</label><input type="text" placeholder="e.g Japan, California" name="location" id="location">
		</fieldset>
	</div>
	
	<div id="reglog" style="display: none;">
		<label for "username">Username:</label><input type="text" placeholder="4-16 characters allowed" maxlength="16" name="username" id="username"><br />
		<label for "password">Password:</label><input type="password" name="password" id="password">
	</div>

	<div id="account" style="display: none;">
		<p class="form_desc">This menu allows you to change the settings on your account.</p>
	</div>
	
	<div id="delete" style="display: none;">
		<p class="form_desc">This will delete your account and all your bookmarks permanently. Are you sure?</p>
	</div>
</body>
</html>