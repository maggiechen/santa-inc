<?php
// it is not necessary for you to use session to save variables, if you want to use 
//session, you need to specify a save path
ini_set('session.save_path','sessions');
session_start(); 

$A_name=$_POST["username"];  //receive username from previous form
$A_pwd=$_POST["pw"];    //receive password from previous form
// assume I have a table administrator which has attributes ANAME and PASSWORD



$oraconn = OCILogon("ora_f8l8", "a40626103", "ug");

//Query the interns first
function getUserAndPW($tab, $uname, $pw) {
	global $A_name, $A_pwd;

	return "select * 
			from ".$tab."  where ".$uname." = '" .$A_name. "' and ".$pw." = 
			'" .$A_pwd. "'";
}



/*
$fulltimequery = "select * 
			from FulltimeElf_mng_mon  where Funame = '" .$A_name. "' and pw = 
			'" .$A_pwd. "'";
$managerquery = "select * 
			from ManagerElf  where Muname = '" .$A_name. "' and pw = 
			'" .$A_pwd. "'";
$unionquery = "select * 
			from UnionWorker  where Uname = '" .$A_name. "' and pw = 
			'" .$A_pwd. "'";

$internquery = "select * 
			from InternElf_train where Iuname = '" .$A_name. "' and pw = 
			'" .$A_pwd. "'"; */
$doquery = OCIParse($oraconn, getUserAndPW("InternElf_train", "Iuname", "pw")) or die("Couldn't parse statement.");
OCIexecute($doquery) or die("Couldn't execute statement.");
$role = "intern";


if (!OCIfetch($doquery)) {

	$doquery = OCIParse($oraconn, getUserAndPW("FulltimeElf_mng_mon", "Funame", "pw")) or die("Couldn't parse statement.");
	OCIexecute($doquery) or die("Couldn't execute statement.");
	$role = "fulltime";
} 

if (!OCIfetch($doquery)){
	$doquery = OCIParse($oraconn, getUserAndPW("ManagerElf", "Muname", "pw")) or die("Couldn't parse statement.");
	OCIexecute($doquery) or die("Couldn't execute statement.");
	$role = "manager";
}

if (!OCIfetch($doquery)) {
	$doquery = OCIParse($oraconn, getUserAndPW("UnionWorker", "Uname", "pw")) or die("Couldn't parse statement.");
	OCIexecute($doquery) or die("Couldn't execute statement.");
	$role = "union";
} else
 {
   // the name and password are not in the table
    echo "<script language='javascript'>alert('Your username and/or password are 
wrong！Please enter again');history.back();</script>";
            exit;
 }


 // the name and password are in the table
switch (role) {
	case "intern":  
		$adminname = OCIresult($doquery, Iuname);
		$password = OCIresult($doquery, pw);
		break;
	case "fulltime":
		$adminname = OCIresult($doquery, Funame);
		$password = OCIresult($doquery, pw);
		break;
	case "manager":
		$adminname = OCIresult($doquery, Muname);
		$password = OCIresult($doquery, pw);
		break;
	case "union":
		$adminname = OCIresult($doquery, Uname);
		$password = OCIresult($doquery, pw);
		break;
}


 
 
   $_SESSION['admin_name']=$A_name;
    $_SESSION['admin_pwd']=$A_pwd;
  echo "<script language='javascript'>alert('Login 
successfully!');window.location.href='homepage.php';</script>"; 
 }
 
?>