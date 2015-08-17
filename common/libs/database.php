<?

// db_query - Run a database query
function db_query($query) {
	if ($query != '') {
		$connect = new mysqli($GLOBALS['auth_db_server'], $GLOBALS['auth_db_user'], $GLOBALS['auth_db_password'], $GLOBALS['auth_db_database']);
		if ($connect->connect_errno) {
			// Couldn't connect to the database
			error_handle('database', 'Connection failed:' . "\r\n" . $connect->connect_error . 'Query:' . "\r\n" . $query, $_SERVER['SCRIPT_FILENAME'], '8');
		} else {
			$result = $connect->query($query);

			if (!$result) {
				// Query has caused an error
				error_handle('database', 'Query error:' . "\r\n" . $connect->error . 'Query:' . "\r\n" . $query, $_SERVER['SCRIPT_FILENAME'], '8');
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

			$connect->close();
		}
	}
}

?>