<style>
		table {
    		border-collapse: collapse;
		}

		table, td, th {
		    border: 1px solid black;
		}
		</style>


<form method = "POST" action = "manageredit.php">
	<input type = "submit" value = "Modify records" name = "modify">
</form>

<form method = "POST" action = "manager.php">
<p>
Select the employee based on # of interns:
<select name="item"> 
<option>minimum</option>
<option>maximum</option>
<option>all</option>

</p>
<p><input type = "submit" value = "Enter" name = "choice"></p>
<p>The "all" option shows all the employees you manage.</p>
</form>


<?php
ini_set('session.save_path','sessions'); //save session to sessions folder
session_start();

$success = True; //keep track of errors so it redirects the page only if there are no errors
$db_conn = OCILogon("ora_f8l8", "a40626103", "ug");  
$u_name=$_SESSION["admin_name"];  //receive username from previous form
$u_pwd = $_SESSION["admin_pwd"];
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

//=======================================================================================================================================
// Connect Oracle...
if ($db_conn) {


	$doquery = executePlainSQL("select uname, pw from ManagerElf where uname = '" .$u_name. "' and pw = 
				'" .$u_pwd. "'");
	if (!OCI_Fetch($doquery)){
		header("location: login.php");
		exit();
	}

	if (array_key_exists('add', $_POST)) {
		header("location: manageredit.php");
		exit();
	}


	if (array_key_exists('choice', $_POST)){
		$makeview = executePlainSQL("CREATE OR REPLACE VIEW numInterns(name, uname, wages, insurance, interncount) AS
									SELECT f.name, f.uname, f.wages, f.insurance, count(i.uname)
									FROM FulltimeElf_mng_mon f, InternElf_train i
									WHERE i.funame = f.uname AND f.muname = '".$u_name."'
									GROUP BY f.uname, f.wages, f.insurance, f.name");
		
		if ($_POST['item'] == "minimum"){
			$intmin = executePlainSQL("SELECT name, uname, wages, insurance, interncount
									FROM numInterns
									WHERE interncount = (SELECT MIN(interncount)
														FROM numInterns)");
			echo "<br>Worker(s) with least interns:<br>";
			echo "<table>";
			echo "<tr><th>Name</th><th>Username #</th><th>Wages</th><th>Insurance</th><th># Interns</th></tr>";
			while ($row = OCI_Fetch_Array($intmin, OCI_BOTH)) {
				echo "<tr><td>" . $row["NAME"] . "</td><td>" . $row["UNAME"] . "</td><td>".$row["WAGES"]."</td><td>".$row["INSURANCE"]."</td><td>".$row["INTERNCOUNT"]."</td></tr>"; //or just use "echo $row[0]" 
			}
			echo "</table>";
		} else if ($_POST['item'] == "maximum"){
			$intmin = executePlainSQL("SELECT name, uname, wages, insurance, interncount
									FROM numInterns
									WHERE interncount = (SELECT MAX(interncount)
														FROM numInterns)");
			echo "<br>Worker(s) with most interns:<br>";
			echo "<table>";
			echo "<tr><th>Name</th><th>Username #</th><th>Wages</th><th>Insurance</th><th># Interns</th></tr>";
			while ($row = OCI_Fetch_Array($intmin, OCI_BOTH)) {
				echo "<tr><td>" . $row["NAME"] . "</td><td>" . $row["UNAME"] . "</td><td>".$row["WAGES"]."</td><td>".$row["INSURANCE"]."</td><td>".$row["INTERNCOUNT"]."</td></tr>"; //or just use "echo $row[0]" 
			}
			echo "</table>";
		} else if ($_POST['item'] == "all") {
			$allcount = executePlainSQL ("SELECT * FROM numInterns");
			echo "<br># interns for all workers:<br>";
			echo "<table>";
			echo "<tr><th>Name</th><th>Username #</th><th>Wages</th><th>Insurance</th><th># Interns</th></tr>";
			while ($row = OCI_Fetch_Array($allcount, OCI_BOTH)) {
				echo "<tr><td>" . $row["NAME"] . "</td><td>" . $row["UNAME"] . "</td><td>".$row["WAGES"]."</td><td>".$row["INSURANCE"]."</td><td>".$row["INTERNCOUNT"]."</td></tr>"; //or just use "echo $row[0]" 
			}
			echo "</table>";
		}

	}


	echo "<p>Interns that your fulltime workers train</p>";
	$internsquery = executePlainSQL("select distinct * from FulltimeElf_mng_mon f, InternElf_train i where f.uname = i.funame and muname = '".$u_name."'");
	echo "<table>";
	echo "<tr><th>Name</th><th>Username</th><th>Institution</th><th>Student number</th><th>Start date(YY-MM-DD)</th><th>Duration</th></tr>";
	while ($row = OCI_Fetch_Array($internsquery, OCI_BOTH)) {
		echo "<tr><td>".$row["NAME"]."</td><td>".$row["UNAME"]."</td><td>".$row["INSTITUTION"]."</td><td>".$row["SID"]."</td><td>".$row["STARTDATE"]."</td><td>".$row["DURATION"]." months</td>";
	}
	echo "</table>";
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
