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
		</p>
		<p><input type = "submit" value = "Change" name = "updateintern"></p>
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
//=========================================================================================================================
function executeBoundSQL($cmdstr, $list) {
	/* Sometimes a same statement will be excuted for severl times, only
	 the value of variables need to be changed.
	 In this case you don't need to create the statement several times; 
	 using bind variables can make the statement be shared and just 
	 parsed once. This is also very useful in protecting against SQL injection. See example code below for       how this functions is used */

	global $db_conn, $success;
	$statement = OCIParse($db_conn, $cmdstr);

	if (!$statement) {
		echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
		$e = OCI_Error($db_conn);
		echo htmlentities($e['message']);
		$success = False;
	}

	foreach ($list as $tuple) {
		foreach ($tuple as $bind => $val) {
			//echo $val;
			//echo "<br>".$bind."<br>";
			OCIBindByName($statement, $bind, $val);
			unset ($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype

		}

		$r = OCIExecute($statement, OCI_DEFAULT);
		if (!$r) {
			echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
			$e = OCI_Error($statement); // For OCIExecute errors pass the statementhandle
			echo htmlentities($e['message']);
			echo "<br>";
			$success = False;
		}
	}

}
//==========================================vvvv MODIFY THIS vvv==================================================================
//TODO
//Modify this so it prints out stuff depending on what you asked for it
function printResult($result) { //prints results from a select statement
	echo "<br>Got data from table tab1:<br>";
	echo "<table>";
	echo "<tr><th>ID</th><th>Name</th></tr>";

	while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
		echo "<tr><td>" . $row["NID"] . "</td><td>" . $row["NAME"] . "</td></tr>"; //or just use "echo $row[0]" 
	}
	echo "</table>";

}
//=======================================================================================================================================
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
		
		$interns = executePlainSQL("select * from InternElf_train");
		echo "<p>Interns you train:</p>";
		echo "<table>";
		echo "<tr><th>Name</th><th>Username</th><th>Institution</th><th>Student number</th><th>Start date(YY-MM-DD)</th><th>Duration</th></tr>";

		while ($row = OCI_Fetch_Array($interns, OCI_BOTH)) {
			echo "<tr><td>".$row["NAME"]."</td><td>".$row["UNAME"]."</td><td>".$row["INSTITUTION"]."</td><td>".$row["SID"]."</td><td>".$row["STARTDATE"]."</td><td>".$row["DURATION"]." months</td></tr>"; 
		}
		echo "</table>"; 

		echo "<p>These children don't have toys yet:</p>";
		$notoys = executePlainSQL("SELECT c.cname, c.rating, c.age, c.cid, c.lat, c.lon FROM Child c LEFT OUTER JOIN Toy_isFor t ON t.CID=c.CID WHERE t.status IS NULL");


		echo "<p>Toyless children :(</p>";
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