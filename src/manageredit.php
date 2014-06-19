<!-- back button -->
<form method="POST" action="manager.php">
<p><input type="submit" value="Back" name="Back"></p>
</form>
<!-- logout button -->
<form method="POST" action="logout.php">
<p><input type="submit" value="Log Out" name="Log Out"></p>
</form>

<?php

ini_set('session.save_path','sessions'); //save session to sessions folder
session_start();

$success = True; //keep track of errors so it redirects the page only if there are no errors
$db_conn = OCILogon("ora_f8l8", "a40626103", "ug");  
//========================================================================================================================
//receive username from previous form
$M_Uname=$_SESSION["admin_name"];  
$M_Pw = $_SESSION["admin_pwd"];

//Getting the input data from the forms for Adding Employee
$E_Name = $_POST['employeeuname'];
$E_UName = $_POST['empname'];
$E_APw = $_POST['apw'];
$E_Wage = $_POST['uwage'];
$E_Ins = $_POST['uins'];
$E_UWorker = $_POST['uuniname'];

//Getting the input data from the forms for Adding Interns
$I_name = $_POST['iuname'];
$I_UName = $_POST['iuname'];
$I_APw = $_POST['ipw'];
$I_Inst = $_POST['insti'];
$I_SID = $_POST['SID'];
$I_Trainer = $_POST['tuname'];
$I_Dur = $_POST['duration'];
$I_SDate = $_POST['sDate'];

//Getting the data for updating Employees
$E_UPname = $_POST['modEname'];
$E_UWage = $_POST['modEwage'];
$E_UIns = $_POST['modEIns'];
$E_UUniname = $_POST['modEUniname'];

//Getting data for updating Interns
$I_UPName = $_POST['modIname'];
$I_UTrainer = $_POST['modItrainer'];

//Getting data for deletions
$D_Emp = $_POST['delEmployee'];
$D_Int = $_POST['delIntern'];
$D_SModel = $_POST['delSModel'];
$D_SSserial = $_POST['delSSerial'];



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
		//echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
		echo "<script type='text/javascript'>alert('Change denied: ".$message."');</script>";

		$e = oci_error($statement); // For OCIExecute errors pass the statementhandle
		//echo htmlentities($e['message']);
		$success = False;
	} else {

	}
	return $statement;

}
//=========================================================================================================================
function executeBoundSQL($cmdstr, $list, $message) {
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
			echo "<script type='text/javascript'>alert('Change denied: ".$message."');</script>";

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
	$qqq = executePlainSQL("select uname, pw from ManagerElf where uname = '" .$M_Uname. "' and pw = '" .$M_Pw. "'");

	if (!OCI_Fetch($qqq)) {
		header("location: login.php");
		exit();
	}



	if (array_key_exists('submitEmployee', $_POST)) {			//Add employees to the table
		executePlainSQL("insert into Username values('".$E_UName."')");
		oci_commit($db_conn);
		$DumpValuesInEmployee = executeBoundSQL("insert into FulltimeElf_mng_mon values (" .$M_UName. "," .$E_UName. "," .$E_APw. "," .$E_Wage. "," .$E_Ins. "," .$E_UWorker. "," .$E_Name. ")");  		
		echo"<br> Added new employee </br>";
		oci_commit($db_conn);
	}
	
	if (array_key_exists ('submitIntern', $_POST)) {
		executePlainSQL("insert into Username values('".$I_UName."')");
		oci_commit($db_conn);
		$DumpValuesInIntern = executeBoundSQL("insert into InternElf_train values (" .$I_UName. "," .$I_APw. "," .$I_Inst. "," .$I_SID. "," .$I_Trainer. "," .$I_name. "," .$I_Dur. "," .$I_SDate. ")", "Interns cannot work more than 12 months."); 
		oci_commit($db_conn);
		$check=executePlainSQL("select * from InternElf_train where uname = '".$I_UName."'");
		if (!OCI_Fetch($check)) {
			executePlainSQL("delete from Username where uname = '".$I_UName."'");
			oci_commit($db_conn);
		}
	}

	if (array_key_exists('submitEUpdate', $_POST)) {
		echo "update FulltimeElf_mng_mon set wages = '".$E_UWage."', insurance = '".$E_UIns."' where uname = '".$E_UPName."'";
		executePlainSQL("update FulltimeElf_mng_mon set wages = '".$E_UWage."', insurance = '".$E_UIns."' where uname = '".$E_UPName."'");
		oci_commit($db_conn);
	}

	if (array_key_exists('submitIUpdate', $_POST)) {
		executePlainSQL("update InternElf_train set funame = '".$I_UTrainer."'where uname = '".$I_UPName."'");
		oci_commit($db_conn);
	}
	if (array_key_exists ('delEmp' , $_POST)) {
		executePlainSQL("delete from FulltimeElf_mng_mon where uname = '".$D_Emp."'", "You will have to remove all of the interns trained by this employee first.");
		OCICommit($db_conn);
	}
	if (array_key_exists ('delInt' , $_POST)) {
		executePlainSQL("delete from InternElf_train where uname = '".$D_Int."'", "You cannot fire an intern until after they begin work");
		OCICommit($db_conn);
	}
	if (array_key_exists ('delSleigh' , $_POST)) {
		executePlainSQL("delete from Sleigh where sModel = " .$D_SModel. " and sSerial = " .$D_SSerial, "This sleigh still has deliveries to make");
		OCICommit($db_conn);
	}
	if (array_key_exists ('delC' , $_POST)) {
		executePlainSQL("delete from Child where CID = ".$D_Child);
		OCICommit($db_conn);
	}
	if (array_key_exists ('delToy' , $_POST)) {
		executePlainSQL("delete from Toy_isFor where iModel = ".$D_TModel. " and iSerial = " .$D_TSno);
		OCICommit($db_conn);
	}	
	if (array_key_exists ('delR' , $_POST)) {
		executePlainSQL("delete from Reindeer_drives where stall = ".$D_Rein);
		OCICommit($db_conn);
	}

	$query = executePlainSQL("select * from Sleigh");
	echo "<table>"; 
	echo "<tr><th>Sleigh name</th><th>Condition</th><th>Sleigh Model</th><th>Sleigh Serial</th></tr>";

	while ($row = OCI_Fetch_Array($query, OCI_BOTH)){
		echo "<tr><td>".$row["SNAME"]."</td><td>".$row["CONDITION"]."</td><td>".$row["SMODEL"]."</td><td>".$row["SSERIAL"]."</td></tr>"; 
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


<style type = "text/css">
table {
	border-collapse: collapse;
}

table, td, th {
	border: 1px solid black;
}

body {
    background-color: #EEE8AA;
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
background: #FFDF00;
z-index: 9999;
border: 1px solid #ccc;
border-bottom: 0px;
-moz-border-radius-topleft: 10px;
border-top-left-radius: 10px;
-moz-border-radius-topright: 10px;
border-top-right-radius: 10px;
width: 130px;
color: #000000;
text-decoration: none;
font-weight: bold;
}

#tabs ul li a:hover {
color: #8B4513;
cursor: hand;
}

#tabs #Content_Area {
padding: 10px;
clear:both;
overflow: overflow;
line-height:19px;
position: relative;
top: 0px;
z-index: 5;
height: 500px;
overflow: overflow;
}

p { padding-left: 15px; }
</style>	

<script type="text/javascript">
function tab(tab) {
document.getElementById('tab1').style.display = 'none';
document.getElementById('tab2').style.display = 'none';
document.getElementById('tab3').style.display = 'none';
document.getElementById('tab4').style.display = 'none';
document.getElementById('tab5').style.display = 'none';
document.getElementById('li_tab1').setAttribute("class", "");
document.getElementById('li_tab2').setAttribute("class", "");
document.getElementById('li_tab3').setAttribute("class", "");
document.getElementById('li_tab4').setAttribute("class", "");
document.getElementById('li_tab5').setAttribute("class", "");
document.getElementById(tab).style.display = 'block';
document.getElementById('li_'+tab).setAttribute("class", "active");
}
</script>

<div id="tabs">
<ul>
<li id="li_tab1" onclick="tab('tab1')"><a>Add an Employee</a></li>
<li id="li_tab2" onclick="tab('tab2')"><a>Add an Intern</a></li>
<li id="li_tab3" onclick="tab('tab3')"><a>Update Employee</a></li>
<li id="li_tab4" onclick="tab('tab4')"><a>Update Intern</a></li>
<li id="li_tab5" onclick="tab('tab5')"><a>Deletions</a></li>

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
<tr><td><th>Employee's username </th></td><td><input type = "text", name = "modEname"> </td></tr>
<tr><td><th>Wage</th></td> <td><input type = "text", name = "modEwage"> </td></tr>
<tr><td><th>Insurance</th></td><td> <input type = "text", name = "modEIns"></td></tr>
</table>
<p><input type = "submit" value = "Update" name = "submitEUpdate"> </p>
</form>
</p>
</div>

<div id="tab4" style="display: none;">
<form method = "POST" action = "manageredit.php">
<br/>
<br/>
<table>
<tr><td><th>Intern's username</th></td><td> <input type = "text", name = "modIname"></td></tr>
<tr><td><th>Trainer assigned</th></td><td> <input type = "text", name = "modItrainer"></td></tr>

</table><p> <input type = "submit" value = "Update" name = "submitIUpdate"> </p>
</form>
</p>
</div>

<div id="tab5" style="display: none;">
<p> All deletions are FINAL. Please be careful while deleting! </p>
<p> Delete by: </p>
<form method = "POST" action = "manageredit.php">
<br/>
<br/>
<table>
<tr><td><input type = "submit" value = "Employee" name = "delEmp"> Username </td> <td> <input type = "text", name = "delEmployee"> </td></tr>
<tr><td> <input type = "submit" value = "Intern" name = "delInt"> Username </td> <td> <input type = "text", name = "delIntern"> </td></tr>
<tr><td> <input type = "submit" value = "Sleigh" name = "delSleigh"> Model </td> <td> <input type = "text", name = "delSModel"> </td></tr>
<tr><td>Serial</td> <td> <input type = "text", name = "delSSerial"> </td></tr>
</table>



</form>
</div>
</div>


