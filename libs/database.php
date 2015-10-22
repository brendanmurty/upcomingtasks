<?

// db_clean - Clean content for use in a database query
function db_clean($db_connect, $dirty) {
    $clean = "'" . mysqli_real_escape_string($db_connect, $dirty) . "'";
    return $clean;
}

// db_connect - Initialise a connection to the database
function db_connect() {
    $connect = new mysqli($GLOBALS['auth_db_server'], $GLOBALS['auth_db_user'], $GLOBALS['auth_db_password'], $GLOBALS['auth_db_database']);
    if ($connect->connect_errno) {
	// Couldn't connect to the database
	error_handle('database', 'Connection failed:' . "\r\n" . $connect->connect_error . 'Query:' . "\r\n" . $query, $_SERVER['SCRIPT_FILENAME'], '');
	return false;
    } else {
        return $connect;
    }
}

// db_disconnect - Close a database connection
function db_disconnect($db_connect) {
    if ($db_connect) {
        $db_connect->close();
    }
}

// db_query - Run a database query
function db_query($db_connect, $query) {
	if ($db_connect && $query) {
		$result = $db_connect->query($query);

		if (!$result) {
			// Query has caused an error
			error_handle('database', 'Query error:' . "\r\n" . $db_connect->error . 'Query:' . "\r\n" . $query, $_SERVER['SCRIPT_FILENAME'], '');
			return false;
		} else {
			// Query success, return the result if required
			if (strpos(strtolower($query),'select ') !== false) {
				if($result->num_rows>1){
					$i = 0;
					while($row=$result->fetch_assoc()){
					    $ret[] = $row;
					    $i++;
					}
					return $ret;
				}else{
					return $result->fetch_assoc();
				}
			}
		}
	}

    return false;
}

?>
