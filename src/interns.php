<!-- php access file for interns-->
<p>
<form method = "POST", action = "interns.php">		
</p>

<p> <input type = "submit", value = "Your Trainer id", name = "tId"> </p>
<p> <input type = "submit", value = "Reindeer under your care", name = "reindeerstuff"> </p>
<p> Search reindeer by sleigh:&nbsp;<input type = "text",name = "reindeerS"> </p>
<p> <input type = "submit", value = "Search", name = "submit"> </p>

<?php

//this tells the system that it's no longer just parsing 
//html; it's now parsing PHP! 
//=========================================================================================================================
$success = True; //keep track of errors so it redirects the page only if there are no errors
//$db_conn = OCILogon("Username", "Password", "ug");  <<-- THIS WONT WORK FOR NOW CAUSE IDK HOW TO DO IT
$A_name=$_POST["username"];  //receive username from previous form
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
	/* Sometimes a same statement will be executed for several times, only
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
//==========================================vvvv MODIFY THIS FUCKER vvv==================================================================
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

	if (array_key_exists('tId', $_POST)) {	//Request Trainer ID
		echo "<br> Trainer name <br>"; 
		executePlainSQL("select i.Funame from InternElf_train i where Iuname =" .$A_name.);	 //TODO I think i fixed it?
		OCICommit($db_conn);

	} else
		if (array_key_exists('reindeerstuff', $_POST)) {	//Request reindeer tuple info
			echo"<br> Reindeer info<br>";
			executePlainSQL("select * from Reindeer_drives r, takeCareOf t where t.Iuname =" .$A_name.); //TODO	- put in username variable --- this ought to work?
			OCICommit($db_conn);

		} else														//TODO how do i do this one .-.	
			if (array_key_exists('reindeerSleigh', $_POST)) {			//Request reindeer info given sleigh
				$sleighName  = $_POST["reindeerS"];  //Get the sleighname from the form
				executePlainSQL("select * from Reindeer_drives r, ");
				OCICommit($db_conn);    //nonsensical comment
			}

	if ($_POST && $success) {
		//POST-REDIRECT-GET
		header("location: interns.php");
	} else {
		// Select data...
		//$result = executePlainSQL("select * from tab1");
		//printResult($result);
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
