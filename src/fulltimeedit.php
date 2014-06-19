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

<!-- logout button -->
<form method="POST" action="logout.php">
<p><input type="submit" value="Log Out" name="Log Out"></p>
</form>

<form method = "POST" action = "fulltimeedit.php">
	<p>
		Delete an intern:
	</p>
	<p>
	<input type = "text" name = "deleteiuname">
	<input type = "submit" value = "Delete intern" name = "delin">
	</p>
</form>

<p>Assign a toy to a child as well as a sleigh to deliver it.</p>
<form method = "POST" action = "fulltimeedit.php">
	<table>
		<tr>
			<td>CID</td><td><input type = "text" name = "cid"></td>
		</tr>
		<tr>
			<td>Item Model</td><td><input type = "text" name = "imodel"></td>
		</tr>
		<tr>
			<td>Item Serial</td><td><input type = "text" name = "iserial"></td>
		</tr>
		<tr>
			<td>Toy Name</td><td><input type = "text" name = "itemname"></td>
		</tr>
		<tr>
			<td>Rating</td><td><input type = "text" name = "rating"></td>
		</tr>
		<tr>
			<td>Sleigh Model</td><td><input type = "text" name = "smodel"></td>
		</tr>
		<tr>
			<td>Sleigh Serial</td><td><input type = "text" name = "sserial"></td>
		</tr>
		<tr>
			<td></td><td><input type = "submit" value = "Add toy" name = "addtoy"></td>
		</tr>
	</table>

</form>


<?php
ini_set('session.save_path','sessions'); //save session to sessions folder
session_start();

$success = True; //keep track of errors so it redirects the page only if there are no errors
$db_conn = OCILogon("ora_f8l8", "a40626103", "ug");  
$u_name=$_SESSION["admin_name"];  //receive username from previous form
$u_pw = $_SESSION["admin_pwd"];
//echo $u_name;
//=========================================================================================================================

function executePlainSQL($cmdstr, $message) { //takes a plain (no bound variables) SQL command and executes it
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
		echo "<script type='text/javascript'>alert('Change denied: ".$message."');</script>";
		//echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
		$e = oci_error($statement); // For OCIExecute errors pass the statementhandle
		//echo htmlentities($e['message']);
		$success = False;
	} else {

	}
	return $statement;

}

// Connect Oracle...
if ($db_conn) {
	
	$doquery = executePlainSQL("select uname, pw from FulltimeElf_mng_mon where uname = '" .$u_name. "' and pw = 
				'" .$u_pw. "'");
	if (!OCI_Fetch($doquery)){
		header("location: login.php");
		exit();
	}


	if (array_key_exists('addtoy', $_POST)) {
		$CID = $_POST['cid'];
		$iModel = $_POST['imodel'];
		$iSerial = $_POST['iserial'];
		$itemName = $_POST['itemname'];
		$rating = $_POST['rating'];
		$sModel = $_POST['smodel'];
		$sSerial = $_POST['sserial'];

		executePlainSQL("insert into Item values ('".$itemName."',".$iModel.",".$iSerial.")");
		oci_commit($db_conn);
		executePlainSQL("insert into Toy_isFor values (".$iModel.",".$iSerial.",".$rating.", 0,".$sModel.",".$sSerial.",".$CID.")");
		oci_commit($db_conn);
	}

	if (array_key_exists('delin', $_POST)) {
		$internToDelete = $_POST['deleteiuname'];
		executePlainSQL("delete from InternElf_train where uname = '".$internToDelete."'", "You cannot fire an intern until they start work");
		OCICommit($db_conn);
	}

	if (array_key_exists('updateintern', $_POST)){
		$newdur = $_POST['dur'];
		$iuname = $_POST['iuname'];

		executePlainSQL("update InternElf_train set duration =".$newdur." where uname = '".$iuname."'", "12 months internship maximum");
		OCICommit($db_conn);
	}
	if ($_POST && $success) {
		header("location: fulltimeedit.php");
	} else {
		// Select data...
		
		$interns = executePlainSQL("select * from InternElf_train i where i.funame = '".$u_name."'", "Sorry, something went wrong");
		echo "<p>Interns you train:</p>";
		echo "<table>";
		echo "<tr><th>Name</th><th>Username</th><th>Institution</th><th>Student number</th><th>Start date(YY-MM-DD)</th><th>Duration</th><th>Stalls they take care of</th></tr>";

		while ($row = OCI_Fetch_Array($interns, OCI_BOTH)) {
			$internuser = $row["UNAME"];
			echo "<tr><td>".$row["NAME"]."</td><td>".$internuser."</td><td>".$row["INSTITUTION"]."</td><td>".$row["SID"]."</td><td>".$row["STARTDATE"]."</td><td>".$row["DURATION"]." months</td>";
			
			$stallsquery = executePlainSQL("select * from takeCareOf where iuname ='".$internuser."'", "Sorry, something went wrong");
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
		$allstalls = executePlainSQL("select t.stall from takeCareOf t, InternElf_train i where t.iuname = i.uname and i.funame = '".$u_name."'", "Sorry, something went wrong");
		while ($row = OCI_Fetch_Array($allstalls,OCI_BOTH)) {
			echo "<p>".$row[0]."</p>";
		}
		echo "</div>";

		echo "<p>Available sleighs</p>";
		$sleighquery = executePlainSQL("select * from Sleigh where condition <> 2 and condition <> 3"); //not damaged/unusable
		echo "<table>"; 
		echo "<tr><th>Sleigh name</th><th>Condition</th><th>Sleigh Model</th><th>Sleigh Serial</th></tr>";

		while ($row = OCI_Fetch_Array($sleighquery, OCI_BOTH)){
			echo "<tr><td>".$row["SNAME"]."</td><td>".$row["CONDITION"]."</td><td>".$row["SMODEL"]."</td><td>".$row["SSERIAL"]."</td></tr>"; 
		}
		echo "</table>";

		$notoys = executePlainSQL("SELECT c.cname, c.rating, c.age, c.cid, c.lat, c.lon FROM Child c LEFT OUTER JOIN Toy_isFor t ON t.CID=c.CID WHERE t.status IS NULL", "Sorry, something went wrong");
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