<?php
// it is not necessary for you to use session to save variables, if you want to use 
//session, you need to specify a save path

//A note about OCI_Fetch_Array: After fetching the result, if you want to access an attribute, you have to use $result["ALLCAPS"] or it won't work

//$tab is the table name, $uname is the attribute for username, $pw is the attribute for pw
function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
	//echo "<br>running ".$cmdstr."<br>";
	global $oraconn, $success;
	$statement = OCIParse($oraconn, $cmdstr); 

	if (!$statement) {
		echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
		$e = OCI_Error($oraconn); // For OCIParse errors pass the       
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

function executeBoundSQL($cmdstr, $list) {
	/* Sometimes a same statement will be excuted for severl times, only
	 the value of variables need to be changed.
	 In this case you don't need to create the statement several times; 
	 using bind variables can make the statement be shared and just 
	 parsed once. This is also very useful in protecting against SQL injection. See example code below for       how this functions is used */

	global $oraconn, $success;
	$statement = OCIParse($oraconn, $cmdstr);

	if (!$statement) {
		echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
		$e = OCI_Error($oraconn);
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

function createQueryString($tab, $uname, $pw) {

	global $A_name, $A_pwd;

	$retval =	"select * 
				from ".$tab."  where ".$uname." = '" .$A_name. "' and ".$pw." = 
				'" .$A_pwd. "'";
 	echo "About to execute: ".$retval."\r\n";
	return $retval;
}

function printResult($result) { //prints results from a select statement
	echo "<br>Got data from table<br>";
	echo "<table>";
	echo "<tr><th>Uname</th><th>Pw</th></tr>";
//TODO: MUST CHANGE IUNAME IF YOU WANT TO SEE OTHER USERNAMES
	while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
		echo "<tr><td>" . $row["IUNAME"] . "</td><td>" . $row["PW"] . "</td></tr>"; //or just use "echo $row[0]" 
	}
	echo "</table>";
	echo "<br></br>"; 

}

ini_set('session.save_path','sessions'); //save session to sessions folder
session_start(); 

$A_name=$_POST["username"];  //receive username from previous form
$A_pwd=$_POST["pw"];    //receive password from previous form
// assume I have a table administrator which has attributes ANAME and PASSWORD


$success = true;
$oraconn = OCILogon("ora_f8l8", "a40626103", "ug");



//Query the interns first

if ($oraconn) {
	$doquery = executePlainSQL(createQueryString("InternElf_train", "Iuname", "pw"));
	
	$role = "intern";

	echo"<br>Intern table result:</br>";
	printResult($doquery);

	if (OCI_Fetch($doquery)) {
		$doquery = executePlainSQL(createQueryString("FulltimeElf_mng_mon", "Funame", "pw"));
		$role = "fulltime";

		echo"<br>Fulltime table result:</br>";
		printResult($doquery);

	} 


	if (OCI_Fetch($doquery)){
		$doquery = executePlainSQL(createQueryString("ManagerElf", "Muname", "pw"));
		$role = "manager";

		echo"<br>Manager table result:</br>";
		printResult($doquery);
	}

	if (OCI_Fetch($doquery)){
		$doquery = executePlainSQL(createQueryString("UnionWorker", "Uname", "pw"));
		$role = "union";


		echo"<br>Union table result:</br>";
		printResult($doquery);
	}

	if(OCI_Fetch($doquery)){
	   // the name and password are not in the table
	  echo "Sorry, that's not a correct username/password combination.\r\n";
	  echo "You entered the username ".$A_name." and the password ".$A_pwd.".\r\n";
	   //header("location: login.php");

	 }

	/* //Test query to see if parsing and executing an SQL statement works
	$tryquery = OCIParse($oraconn, "select * from InternElf_train");
	OCIexecute($tryquery, OCI_DEFAULT);
	if (OCIfetch($tryquery)) {
		echo "Try worked";
		printResult($tryquery);
	}
	*/
	echo "You are a ".$role;

	OCILogoff($oraconn);

	 
	   $_SESSION['admin_name']=$A_name;
	   $_SESSION['admin_pwd']=$A_pwd;
	  
		//header("location: santa-inc.php");
 } else {
	echo "cannot connect";
	$e = OCI_Error(); // For OCILogon errors pass no handle
	echo htmlentities($e['message']);
}
 
?>