
<p> Search reindeer by sleigh:</p>
<p>
<form method = "POST" action = "unionworkers.php">
<p><input type = "text" name = "reindeerSleigh"> </p>
<p> <input type = "submit" value = "Search" name = "submit"> </p>
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

	//Print the name of the intern's trainer
	echo "<br> Trainer name: <br>"; 
	$trainernamequery = executePlainSQL("select f.name as name from InternElf_train i, FulltimeElf_mng_mon f where i.funame = f.uname and i.uname = '".$u_name."'");	
	while ($row = OCI_Fetch_Array($trainernamequery, OCI_BOTH))
		echo "<p>".$row[0]."</p>";


	
	$reindeerquery = executePlainSQL("select * from takeCareOf t, Reindeer_drives r where t.stall = r.stall and t.iuname = '".$u_name."'");

	echo "<br>Reindeer under your care:<br>";
	echo "<table>";
	echo "<tr><th>Name</th><th>Stall #</th><th>Diet</th><th>Sleigh model</th><th>Sleigh Serial</th></tr>";
	while ($row = OCI_Fetch_Array($reindeerquery, OCI_BOTH)) {
		echo "<tr><td>" . $row["NAME"] . "</td><td>" . $row["STALL"] . "</td><td>".$row["DIET"]."</td><td>".$row["SMODEL"]."</td><td>".$row["SSERIAL"]."</td></tr>"; //or just use "echo $row[0]" 
	}
	echo "</table>";
	
	$countreindeerquery = executePlainSQL("select count(r.stall) from takeCareOf t, Reindeer_drives r where t.stall = r.stall and t.iuname = '".$u_name."'");
	$row = OCI_Fetch_Array($countreindeerquery, OCI_BOTH);
	echo "<p> You take care of <b>".$row[0]."</b> reindeer.</p>";


	if (array_key_exists('reindeerSleigh', $_POST)) {			//Request reindeer info given sleigh
		$sleighName  = $_POST["reindeerSleigh"];  //Get the sleighname from the form
		$reinsleighquery = executePlainSQL("select * from Reindeer_drives r, Sleigh s where s.sName like '%" .$sleighName. "%' and s.sModel = r.sModel and s.sSerial = r.sSerial");		

		echo "<br>Search results for '".$sleighName."'': <br>";
		echo "<table>";
		echo "<tr><th>Name</th><th>Stall #</th><th>Diet</th><th>Sleigh model</th><th>Sleigh Serial</th></tr>";
		while ($row = OCI_Fetch_Array($reinsleighquery, OCI_BOTH)) {
			echo "<tr><td>" . $row["NAME"] . "</td><td>" . $row["STALL"] . "</td><td>".$row["DIET"]."</td><td>".$row["SMODEL"]."</td><td>".$row["SSERIAL"]."</td></tr>"; //or just use "echo $row[0]" 
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

/* OCILogon() allows you to log onto the Oracle database
     The three arguments are the username, password, and database
     You will need to replace "username" and "password" for this to
     to work. 
     all strings that start with "$" are variables; they are created
     implicitly by appearing on the left hand side of an assignment 
     statement */

/* OCIParse() Prepares Oracle statement for execution
      The two arguments are the connection and SQL query. */
/* OCIExecute() executes a previously parsed statement
      The two arguments are the statement which is a valid OCI
      statement identifier, and the mode. 
      default mode is OCI_COMMIT_ON_SUCCESS. Statement is
      automatically committed after OCIExecute() call when using this
      mode.
      Here we use OCI_DEFAULT. Statement is not committed
      automatically when using this mode */

/* OCI_Fetch_Array() Returns the next row from the result data as an  
     associative or numeric array, or both.
     The two arguments are a valid OCI statement identifier, and an 
     optinal second parameter which can be any combination of the 
     following constants:

     OCI_BOTH - return an array with both associative and numeric 
     indices (the same as OCI_ASSOC + OCI_NUM). This is the default 
     behavior.  
     OCI_ASSOC - return an associative array (as OCI_Fetch_Assoc() 
     works).  
     OCI_NUM - return a numeric array, (as OCI_Fetch_Row() works).  
     OCI_RETURN_NULLS - create empty elements for the NULL fields.  
     OCI_RETURN_LOBS - return the value of a LOB of the descriptor.  
     Default mode is OCI_BOTH.  */
?>
