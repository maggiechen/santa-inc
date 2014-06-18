<style>
		table {
    		border-collapse: collapse;
		}

		table, td, th {
		    border: 1px solid black;
		}
		</style>

<p>To change the internship duration of your intern, enter a username and new duration. Please note that internships cannot last longer than 12 months.</p>
<p>
	<form method = "POST" action = "fulltimeedit.php">
		<p>
			Username: <input type = "text" name = "iuname">
			New duration: <input type = "text" name = "dur"> 
			<input type = "submit" value = "Change" name = "updateintern">
		</p>
	</form>
</p>

<?php
ini_set('session.save_path','sessions'); //save session to sessions folder
session_start();

$success = True; //keep track of errors so it redirects the page only if there are no errors
$db_conn = OCILogon("ora_f8l8", "a40626103", "ug");  
$u_name=$_SESSION["admin_name"];  //receive username from previous form
//echo $u_name;
//=========================================================================================================================

function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
	//echo "<br>running ".$cmdstr."<br>";
	global $db_conn, $success;
	$statement = OCIParse($db_conn, $cmdstr); 
	if (!$statement) {
		echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
		$e = OCI_Error($db_conn); // For OCIParse errors pass the       
		// connection handle
		echo htmlentities($e['message']);
		$success = False;
	}

	$r = OCIExecute($statement, OCI_DEFAULT);
	if (!$r) {
		echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
		$e = oci_error($statement); // For OCIExecute errors pass the statementhandle
		echo htmlentities($e['message']);
		$success = False;
	} else {

	}
	return $statement;

}

// Connect Oracle...
if ($db_conn) {

	if (array_key_exists('updateintern', $_POST)){
		$newdur = $_POST['dur'];
		$iuname = $_POST['iuname'];

		executePlainSQL("update InternElf_train set duration =".$newdur." where uname = '".$iuname."'");
		OCICommit($db_conn);
	}
	if ($_POST && $success) {
		header("location: fulltimeedit.php");
	} else {
		// Select data...
		
		$interns = executePlainSQL("select * from InternElf_train i where i.funame = '".$u_name."'");
		echo "<p>Interns you train:</p>";
		echo "<table>";
		echo "<tr><th>Name</th><th>Username</th><th>Institution</th><th>Student number</th><th>Start date(YY-MM-DD)</th><th>Duration</th><th>Stalls they take care of</th></tr>";

		while ($row = OCI_Fetch_Array($interns, OCI_BOTH)) {
			$internuser = $row["UNAME"];
			echo "<tr><td>".$row["NAME"]."</td><td>".$internuser."</td><td>".$row["INSTITUTION"]."</td><td>".$row["SID"]."</td><td>".$row["STARTDATE"]."</td><td>".$row["DURATION"]." months</td>";
			
			$stallsquery = executePlainSQL("select * from takeCareOf where iuname ='".$internuser."'");
			echo "<td>";
			while ($row2 = OCI_Fetch_Array($stallsquery, OCI_BOTH)) {
				echo "<p>".$row2["STALL"]." </p>";
			}

			echo "</td>";
			echo "</tr>";
		}
		echo "</table>"; 

		echo "<div>";
		echo "<p><b>Stalls that all of your interns cover:</b></p>";
		$allstalls = executePlainSQL("select t.stall from takeCareOf t, InternElf_train i where t.iuname = i.uname and i.funame = '".$u_name."'");
		while ($row = OCI_Fetch_Array($allstalls,OCI_BOTH)) {
			echo "<p>".$row[0]."</p>";
		}
		echo "</div>";

		$notoys = executePlainSQL("SELECT c.cname, c.rating, c.age, c.cid, c.lat, c.lon FROM Child c LEFT OUTER JOIN Toy_isFor t ON t.CID=c.CID WHERE t.status IS NULL");

		echo "<p>These children don't have toys yet:</p>";		
		echo "<table>";
		echo "<tr><th>Name</th><th>Rating</th><th>Age</th><th>Child ID</th><th>GPS coordinates</th></tr>";

		while ($row = OCI_Fetch_Array($notoys, OCI_BOTH)) {
			echo "<tr><td>".$row["CNAME"]."</td><td>".$row["RATING"]."</td><td>".$row["AGE"]."</td><td>".$row["CID"]."</td><td>".$row["LAT"].", ".$row["LON"]."</td></tr>"; 
		}
		echo "</table>"; 

	}

	
	//Commit to save changes...
	OCILogoff($db_conn);

} else {
	echo "cannot connect";
	$e = OCI_Error(); // For OCILogon errors pass no handle
	echo htmlentities($e['message']);
}


?>