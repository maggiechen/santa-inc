<style type = "text/css">
table {
	border-collapse: collapse;
}

table, td, th {
	border: 1px solid black;
}

#tabs ul {
padding: 0px;
margin: 0px;
margin-left: 10px;
list-style-type: none;
}

#tabs ul li {
display: inline-block;
clear: none;
float: left;
height: 24px;
}

#tabs ul li a {
position: relative;
margin-top: 16px;
display: block;
margin-left: 6px;
line-height: 24px;
padding-left: 10px;
background: #f6f6f6;
z-index: 9999;
border: 1px solid #ccc;
border-bottom: 0px;
-moz-border-radius-topleft: 4px;
border-top-left-radius: 4px;
-moz-border-radius-topright: 4px;
border-top-right-radius: 4px;
width: 130px;
color: #000000;
text-decoration: none;
font-weight: bold;
}

#tabs ul li a:hover {
text-decoration: underline;
}

#tabs #Content_Area {
padding: 10px;
clear:both;
overflow:hidden;
line-height:19px;
position: relative;
top: 0px;
z-index: 5;
height: 500px;
overflow: hidden;
}

p { padding-left: 15px; }
</style>	

<script type="text/javascript">
function tab(tab) {
document.getElementById('tab1').style.display = 'none';
document.getElementById('tab2').style.display = 'none';
document.getElementById('tab3').style.display = 'none';
document.getElementById('li_tab1').setAttribute("class", "");
document.getElementById('li_tab2').setAttribute("class", "");
document.getElementById('li_tab3').setAttribute("class", "");
document.getElementById(tab).style.display = 'block';
document.getElementById('li_'+tab).setAttribute("class", "active");
}
</script>

<div id="tabs">
<ul>
<li id="li_tab1" onclick="tab('tab1')"><a>Add an Employee</a></li>
<li id="li_tab2" onclick="tab('tab2')"><a>Add an Intern</a></li>
<li id="li_tab3" onclick="tab('tab3')"><a>Update Employee</a></li>

</ul>

<div id="Content_Area">
<div id="tab1">
<br/>
<br/>
<form method = "POST" action = "manageredit.php">
<table>
<tr><td><th>Employee name</th></td> <td> <input type = "text", name = "employeeuname"> </td></tr>
<tr><td><th>Assigned Username</th></td> <td><input type = "text", name = "empname"></td></tr>
<tr><td><th>Assigned Password</th></td> <td><input type = "text", name = "apw"> </td></tr>
<tr><td><th>Wage</th></td> <td><input type = "text", name = "uwage"> </td></tr>
<tr><td><th>Insurance</th></td><td> <input type = "text", name = "uins"></td></tr>
<tr><td><th>Union worker username</th></td><td> <input type = "text", name = "uuniname"></td></table>
<p><input type = "submit" value = "Add" name = "submitEmployee"> </p>
</form>
</p>
</div>

<div id="tab2" style="display: none;">
<form method = "POST" action = "manageredit.php">
<table>
<br/>
<br/>
<tr><td><th>Intern name</th></td> <td> <input type = "text", name = "iuname"> </td></tr>
<tr><td><th>Assigned Username</th></td> <td><input type = "text", name = "iname"></td></tr>
<tr><td><th>Assigned Password</th></td> <td><input type = "text", name = "ipw"> </td></tr>
<tr><td><th>Institution</th></td> <td><input type = "text", name = "insti"> </td></tr>
<tr><td><th>Student ID</th></td><td> <input type = "text", name = "SID"></td></tr>
<tr><td><th>Trainer assigned</th></td><td> <input type = "text", name = "tuname"></td></tr>
<tr><td><th>Duration</th></td><td> <input type = "text", name = "duration"></td></tr>
<tr><td><th>Start Date</th></td><td> <input type = "text", name = "sDate"></td></tr>
</table><p> <input type = "submit" value = "Add" name = "submitIntern"> </p>
</form>
</p>
</div>

<div id="tab3" style = "display: none;">
<br/>
<br/>
<form method = "POST" action = "manageredit.php">
<table>
<tr><td><th>Employee name</th></td> <td> <input type = "text", name = "modEname"> </td></tr>
<tr><td><th>Assigned Username</th></td> <td><input type = "text", name = "modEUname"></td></tr>
<tr><td><th>Assigned Password</th></td> <td><input type = "text", name = "modApw"> </td></tr>
<tr><td><th>Wage</th></td> <td><input type = "text", name = "modEwage"> </td></tr>
<tr><td><th>Insurance</th></td><td> <input type = "text", name = "modEIns"></td></tr>
<tr><td><th>Union worker username</th></td><td> <input type = "text", name = "modEUniname"></td></table>
<p><input type = "submit" value = "Add" name = "submitEUpdate"> </p>
</form>
</p>
</div>
</div>
</div>


<?php
ini_set('session.save_path','sessions'); //save session to sessions folder
session_start();

$success = True; //keep track of errors so it redirects the page only if there are no errors
$db_conn = OCILogon("ora_f8l8", "a40626103", "ug");  
//========================================================================================================================
//receive username from previous form
$M_Uname=$_SESSION["admin_name"];  
//Getting the input data from the forms for Employee
$E_Name = $_POST['employeeuname'];
$E_UName = $_POST['empname'];
$E_APw = $_POST['apw'];
$E_Wage = $_POST['uwage'];
$E_Ins = $_POST['uins'];
$E_UWorker = $_POST['uuniname'];

//Getting the input data from the forms for Interns
$I_name = $_POST['iuname'];
$I_UName = $_POST['iuname'];
$I_APw = $_POST['ipw'];
$I_Inst = $_POST['insti'];
$I_SID = $_POST['SID'];
$I_Trainer = $_POST['tuname'];
$I_Dur = $_POST['duration'];
$I_SDate = $_POST['sDate'];
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
	if (array_key_exists('submitEmployee', $_POST)) {			//Add employees to the table
		$DumpValuesInEmployee = executeBoundSQL("insert into FulltimeElf_mng_mon values (" .$M_UName. "," .$E_UName. "," .$E_APw. "," .$E_Wage. "," .$E_Ins. "," .$E_UWorker. "," .$E_Name. ")");  		
		echo"<br> Added new employee </br>";
	}
	
	if (array_key_exists ('submitIntern', $_POST)) {
		$DumpValuesInIntern = executeBoundSQL("insert into InternElf_train values (" .$I_UName. "," .$I_APw. "," .$I_Inst. "," .$I_SID. "," .$I_Trainer. "," .$I_name. "," .$I_Dur. "," .$I_SDate. ")"); 
	
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
