<html>

	<head>
		<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
		<script type="text/javascript" src="js/tablesorter/jquery.tablesorter.js"></script>
		
		<style>
		
			body {
				
				font-family: Verdana, Arial;
				
			}
			
			input {
				
				padding-left: 5px;
				
			}
		
		</style>
		<link href="js\tablesorter\themes\blue\style.css" rel="stylesheet" type="text/css"/>

		<title>WHM/cPanel Server Account Locator</title>
		
	</head>
	
	<body>
	
		<h1>Server Account Location</h1>

		Search for an account: <input id="search" type="search" placeholder="Search" autocomplete="off" autofocus="true" align="middle"/>
		
		<table id="accounts" class="tablesorter">
			<thead>
				<tr>
					<th>Domain</th>
					<th>Username</th>
					<th>Plan</th>
					<th>Server</th>
				</tr>
			</thead>
			<tbody>

<?php
 
	ini_set('max_execution_time', 90000); //300 seconds = 5 minutes
	
	/* Change this part to ensure you list the data from a server which actually exists! */
	$servers = array (

		array("server" => "myserver.domain.com", "username" => "root", "password" => "CHANGEME",),

	);
	
	// No need to change anything else below
	$server_counts = array();
	
	foreach ($servers as $serverdetails) {
		
		$server = $serverdetails["server"];
		$username = $serverdetails["username"];
		$password = $serverdetails["password"];
		
		$server_count = 0;
		$query = 'https://' . $server . ':2087/json-api/listaccts?api.version=1&want=domain';

		$curl = curl_init();       
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);  
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($curl, CURLOPT_HEADER,0);          
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);  
		$header[0] = "Authorization: Basic " . 
		base64_encode($username.":".$password) . "\n\r";
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header); 
		curl_setopt($curl, CURLOPT_URL, $query); 
		$result = curl_exec($curl);
		
		if ($result == false) {
			error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $query<br /><br />"); 
															// log error if curl exec fails
		}


		curl_close($curl);

		$result = json_decode($result, true);

		foreach($result["data"]["acct"] as $apidata) {
			
			echo '<tr data-state="accountlist"><td>';
			print $apidata["domain"];
			echo "</td><td>";
			print $apidata["user"];
			echo "</td><td>";
			print $apidata["plan"];
			echo "</td><td>";
			echo $server;
			echo "</td></tr>";
			$server_count++;
		}
		
		$server_counts[] = array("server" => $server, "server_count" => $server_count);
				
	}
	?>
	
		</tbody>
	</table>
	
	<?php
		foreach ($server_counts as $servercount) {
					
			echo "<b>User Count for: </b>" . $servercount["server"] . " = " . $servercount["server_count"] . "<br />";

		}
		
	?>

	<script type="text/javascript">

		$(document).ready(function() 
			{ 
				$("#accounts").tablesorter({
					
					sortList: [[0,0]]
					
				}); 
			} 
		); 

		$("input#search").keyup(function () {
			var filter = $(this).val();
			var regExPattern = "gi";
			var regEx = new RegExp(filter, regExPattern);
			$("table tr").each(function () {
				if($(this).data('state'))
				if (
				$(this).text().search(new RegExp(filter, "i")) < 0
				&& $(this).data('state').search(regEx) < 0
				){
					$(this).hide();
				} else {
					$(this).show();
				}
			});
		});
		
	</script>
	
	</body>
</html>