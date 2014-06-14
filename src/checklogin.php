<?php
// it is not necessary for you to use session to save variables, if you want to use 
//session, you need to specify a save path

//A note about OCI_Fetch_Array: After fetching the result, if you want to access an attribute, you have to use $result["ALLCAPS"] or it won't work

function getUserAndPW($tab, $uname, $pw) {
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

}

ini_set('session.save_path','sessions');
session_start(); 

$A_name=$_POST["username"];  //receive username from previous form
$A_pwd=$_POST["pw"];    //receive password from previous form
// assume I have a table administrator which has attributes ANAME and PASSWORD



$oraconn = OCILogon("ora_f8l8", "a40626103", "ug");



//Query the interns first


$doquery = OCIParse($oraconn, getUserAndPW("InternElf_train", "Iuname", "pw")) or die("Couldn't parse statement.");
OCIexecute($doquery,OCI_DEFAULT) or die("Couldn't execute statement.");
$role = "intern";

echo"<br>Intern table result:</br>";
printResult($doquery);

if (!OCIFetch($doquery)) {
	$doquery = OCIParse($oraconn, getUserAndPW("FulltimeElf_mng_mon", "Funame", "pw")) or die("Couldn't parse statement.");
	OCIexecute($doquery,OCI_DEFAULT) or die("Couldn't execute statement.");
	$role = "fulltime";

	echo"<br>Fulltime table result:</br>";
	printResult($doquery);

} 


if (!OCIFetch($doquery)){
	$doquery = OCIParse($oraconn, getUserAndPW("ManagerElf", "Muname", "pw")) or die("Couldn't parse statement.");
	OCIexecute($doquery,OCI_DEFAULT) or die("Couldn't execute statement.");
	$role = "manager";
	echo"<br>Manager table result:</br>";
	printResult($doquery);
}

if (!OCIFetch($doquery)){
	$doquery = OCIParse($oraconn, getUserAndPW("UnionWorker", "Uname", "pw")) or die("Couldn't parse statement.");
	OCIexecute($doquery,OCI_DEFAULT) or die("Couldn't execute statement.");
	$role = "union";
	echo"<br>Union table result:</br>";
	printResult($doquery);
}

if(!OCIFetch($doquery)){
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

OCILogoff($db_conn);

 
   $_SESSION['admin_name']=$A_name;
   $_SESSION['admin_pwd']=$A_pwd;
  
	//header("location: santa-inc.php");
 
 
?>