<?php
$success = True;
$db_conn = NULL;

/**
 * This function was taken from the Oracle/PHP tutorial files.
 */
function connectToDB() {
	global $db_conn;

	// Your username is ora_(CWL_ID) and the password is a(student number). For example,
	// ora_platypus is the username and a12345678 is the password.
	$db_conn = OCILogon("ora_platypus", "a12345678", "dbhost.students.cs.ubc.ca:1234/stu");

	if ($db_conn) {
		return true;
	} else {
		$e = OCI_Error(); // For OCILogon errors pass no handle
		echo htmlentities($e['message']);
		return false;
	}
}

/**
 * This function was taken from the Oracle/PHP tutorial files.
 */
function disconnectFromDB() {
	global $db_conn;

	OCILogoff($db_conn);
}

/**
 * This function was taken from the Oracle/PHP tutorial files.
 */
function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
	//echo "<br>running ".$cmdstr."<br>";
	global $db_conn, $success;

	$statement = OCIParse($db_conn, $cmdstr);
	//There are a set of comments at the end of the file that describe some of the OCI specific functions and how they work

	if (!$statement) {
		echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
		$e = OCI_Error($db_conn); // For OCIParse errors pass the connection handle
		echo htmlentities($e['message']);
		$success = False;
	}

	$r = OCIExecute($statement, OCI_DEFAULT);
	if (!$r) {
		echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
		$e = oci_error($statement); // For OCIExecute errors pass the statementhandle
		echo htmlentities($e['message']);
		$success = False;
	}

	return $statement;
}

function generateColumnJSON($result) {
	$returnString = '';
	$ncols = oci_num_fields($result);
	// All but last column
	for ($i = 1; $i < $ncols; ++$i) {
		$colname = oci_field_name($result, $i);
		$returnString .= '"' . $colname . '",';
	}
	// Last column
	if ($i == $ncols) {
		$colname = oci_field_name($result, $i);
		$returnString .= '"' . $colname . '"';
	}
	return $returnString;
}

if (isset($_GET) && isset($_GET['tables']) && connectToDB()) {
	$echoString = "{";
	foreach ($_GET['tables'] as $table) {
		$echoString .= '"' . $table . '":[';
		$result = executePlainSQL("SELECT * FROM {$table}");
		$echoString .= generateColumnJSON($result);
		$echoString .= "],";
	}
	disconnectFromDB();
	$echoString = '{' . substr($echoString, 1, strlen($echoString) - 2) . '}';
	echo $echoString;
} else {
	echo "Invalid GET request given to customQueryHelper.php";
}
?>