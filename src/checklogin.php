<?php
// it is not necessary for you to use session to save variables, if you want to use 
//session, you need to specify a save path
ini_set('session.save_path','sessions');
session_start(); 

$A_name=$_POST["username"];  //receive username from previous form
$A_pwd=$_POST["pw"];    //receive password from previous form
// assume I have a table administrator which has attributes ANAME and PASSWORD
$myselect = "select * from administrator where ANAME = '" .$A_name. "' and PASSWORD = 
'" .$A_pwd. "'";
$oraconn = OCILogon("ora_f0p8", "a38343125", "ug");
$doquery = OCIParse($oraconn, $myselect) or die("Couldn't parse statement.");
OCIexecute($doquery) or die("Couldn't execute statement.");

if (OCIfetch($doquery))
 { // the name and password are in the table
  $adminname = OCIresult($doquery, ANAME);
  $password = OCIresult($doquery, PASSWORD);
 
   $_SESSION['admin_name']=$A_name;
    $_SESSION['admin_pwd']=$A_pwd;
  echo "<script language='javascript'>alert('Login 
successfully!');window.location.href='homepage.php';</script>";   // if succeed, 
move to next page
 }
 else
 {
   // the name and password are not in the table
    echo "<script language='javascript'>alert('Your username and/or password are 
wrong！Please enter again');history.back();</script>";
            exit;
 }
?>