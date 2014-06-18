<!-- this file will reset and populate the databases to their initial configuration -->

<p>If you wish to reset the table press on the reset button. If this is the first time you're running this page, you MUST use reset</p>
<form method="POST" action="reset.php">
   
<p><input type="submit" value="Reset" name="reset"></p>
</form>

<?php

//this tells the system that it's no longer just parsing 
//html; it's now parsing PHP

$success = True; //keep track of errors so it redirects the page only if there are no errors
$db_conn = OCILogon("ora_k4v8", "a31244122", "ug");

function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
	//echo "<br>running ".$cmdstr."<br>";
	global $db_conn, $success;
	$statement = OCIParse($db_conn, $cmdstr); //There is a set of comments at the end of the file that describe some of the OCI specific functions and how they work

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

if ($db_conn) {

	if (array_key_exists('reset', $_POST)) {
		// Drop old tables...
		echo "<br> dropping tables <br>";
		executePlainSQL("Drop table takeCareOf");
		executePlainSQL("Drop table InternElf_train");
		executePlainSQL("Drop table FulltimeElf_mgn_mon");
		executePlainSQL("Drop table UnionWorker");
		executePlainSQL("Drop table ManagerElf");
		executePlainSQL("Drop table Reindeer_drives");
		executePlainSQL("Drop table Toy_isfor");
		executePlainSQL("Drop table Child");
		executePlainSQL("Drop table Sleigh");
		executePlainSQL("Drop table Supplies");
		executePlainSQL("Drop table Supplier");
		executePlainSQL("Drop table Item");

		// Create new table...
		echo "<br> recreating tables <br>";
		executePlainSQL('create table Item(
name char[40],
iSerial integer, 
iModel integer,
PRIMARY KEY(iSerial, iModel));');
		executePlainSQL('create table Supplier(
			lat real, 
			lon real, 
			phone integer, 
			sName char[40],
			PRIMARY KEY(sName))');
		executePlainSQL('create table Supplies(
			price decimal(10,2), 
			supply char[40], 
			suName char[40], 
			iSerial integer, 
			iModel integer, 
			PRIMARY KEY(sName, iSerial, iModel),
			foreign key (suName) references Supplier (suName),
			foreign key (iSerial, iModel) references Item(iSerial, iModel))');
		executePlainSQL('create table Child (
					cName char[40], 
					lat real, 
					lon real, 
					rating integer, 
					age integer, 
					CID integer,
					PRIMARY KEY(CID))');
		executePlainSQL('create table Sleigh(
					sModel integer, 
					sSerial integer, 
					condition integer,
					sName char[40],
					PRIMARY KEY(sModel, sSerial))');
		executePlainSQL('create table Toy_isFor(
						iSerial integer,
						iModel integer,
						rating integer,
						status integer, 
						sModel integer not null,
						sSerial integer not null,
						CID integer not null unique,
						PRIMARY KEY (iSerial, iModel),
						foreign key (iSerial, iModel) references Item (iSerial, iModel),
						foreign key (sModel, sSerial) references Sleigh (sModel, sSerial),
						foreign key (CID) references Child (CID))');
		executePlainSQL('create table UnionWorker(
					UID integer,
					PRIMARY KEY(UID))');
		executePlainSQL('create table ManagerElf(
					MID integer,
					name char[40],
					PRIMARY KEY(MID))');
		executePlainSQL('create table FulltimeElf_mng_mon(
					MID integer not null,
					FID integer,
					wages decimal (10, 2),
					insurance decimal (10, 2),
					UID integer not null,
					name char[40], 
					PRIMARY KEY (FID),
					foreign key (UID) references UnionWorker (UID),
					foreign key (MID) references ManagerElf (MID))');
		
		executePlainSQL('create table InternElf_train(
					institution char[40],
					SID integer,
					FID integer not null,
					name char[40],
					duration integer,
					startDate date,
					PRIMARY KEY (institution, SID),
					foreign key (FID) references FulltimeElf_mng_mon(FID))');
		executePlainSQL('
					create table Reindeer_drives(
					diet char[40], 
					name char[40], 
					stall integer, 
					sModel integer, 
					sSerial integer,
					PRIMARY KEY(stall),
					foreign key(sModel, sSerial) references Sleigh (sModel, sSerial))');
		executePlainSQL('create table takeCareOf(
					institution char[40], 
					SID integer, 
					stall integer,
					PRIMARY KEY (institution, SID, stall),
					foreign key (institution, SID) references InternElf_train(institution, SID),
					foreign key (stall) references Reindeer_drives(stall))');
		
		executePlainSQL('insert into Item values ( "Rocket launcher", 20, 39852)');
		executePlainSQL('insert into Item values ( "Rocket launcher", 20, 2953)');
		executePlainSQL('insert into Item values ( "Pogo stick", 76, 16593)');
		executePlainSQL('insert into Item values ( "Skateboard", 58, 82752)');
		executePlainSQL('insert into Item values ( "Applesoft Quantum Computer", 1, 1)');
		executePlainSQL('insert into Item values ( "Teddy bear", 852, 432108)');
		executePlainSQL('insert into Item values ("Hoola hoop", 32, 65853)');
		executePlainSQL('insert into Item values ( "Bicycle", 6874, 11586)');
		executePlainSQL('insert into Item values ( "Bicycle", 6874, 2857)');
		executePlainSQL('insert into Item values ( "Dollhouse", 21, 423)');
		executePlainSQL('');
		executePlainSQL('');
		executePlainSQL('');
		executePlainSQL('');
		executePlainSQL('');
		executePlainSQL('');
		executePlainSQL('');
		executePlainSQL('');
		executePlainSQL('');
		executePlainSQL('');
		executePlainSQL('');
		executePlainSQL('');
		executePlainSQL('');
		executePlainSQL('');
		executePlainSQL('');
		executePlainSQL('');

		OCICommit($db_conn);

	}

	if ($_POST && $success) {
		//POST-REDIRECT-GET -- See http://en.wikipedia.org/wiki/Post/Redirect/Get
		header("location: reset.php");
	}

	//Commit to save changes...
	OCILogoff($db_conn);
} else {
	echo "cannot connect";
	$e = OCI_Error(); // For OCILogon errors pass no handle
	echo htmlentities($e['message']);
}

?>