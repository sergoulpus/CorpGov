<?

function DBconnect($db_filename) {
	try {
		return new PDO('sqlite:' . $db_filename);
	} catch (Exception $e) {
		var_dump($e);
		die();
	}
}

/*
 * Attach data-type to each field, with meta data from metainfo_* table
 * 
 */

function get_column_info($table, $return_short = false, $include_id = true) {
	global $db;

	if ($return_short !== false AND $return_short !== true) {
		throw new Exception("Ei klapi");
	}




	// return_types -> short array, full array
	$res = selectIntoArray("PRAGMA table_info('" . $table . "')", "assoc");

	// return only list of olumns
	if ($return_short == true) {
		foreach ($res as $r) {
			$cols[$r["name"]] = $r["name"];
		}
	}
	else {
		foreach ($res as $r) {
			$cols[$r["name"]]["name"] = $r["name"];

			if ($r["name"] == 'id' OR strpos($r["name"], "_auto") - strlen($r["name"]) == -5 OR $r["name"] == "firma_id") {
				$cols[$r["name"]]["type"] = "AUTO";
			}
			elseif (strpos($r["name"], "_id") - strlen($r["name"]) == -3 && in_array(substr($r["name"], 0, -3), all_tables())) {
				$cols[$r["name"]]["type"] = "DROP-DOWN";
			}
			else {
				$cols[$r["name"]]["type"] = strtoupper($r["type"]);
			}

			$cols[$r["name"]]["notnull"] = $r['notnull'] ? true : false;
			$cols[$r["name"]]["default_value"] = $r['dflt_value'] ? $r['dflt_value'] : false;

			$cols[$r["name"]]['display_name'] = $r["name"];
			$cols[$r["name"]]['tooltip'] = "";
			$cols[$r["name"]]['validation_regexp'] = "";
			$cols[$r["name"]]['params'] = array();
			$cols[$r["name"]]['fieldset'] = array();
		}

		// Add metainfo
		$metainfo = selectIntoArray("SELECT * FROM metainfo WHERE table_name = '$table'", "assoc");

		foreach ($metainfo as $row) {
			if (!empty($cols[$row["column_name"]])) {


				$cols[$row["column_name"]]['display_name'] = $row['display_name'];
				$cols[$row["column_name"]]['tooltip'] = $row['tooltip'];
				$cols[$row["column_name"]]['validation_regexp'] = $row['validation_regexp'];

				/* Parese and add params and fieldset info */
				$row["params"] = explode(",", str_replace(" ", "", $row["params"]));
				$row["fieldset"] = explode(",", str_replace(" ", "", $row["fieldset"]));

				$cols[$row["column_name"]]['params'] = $row['params'];
				$cols[$row["column_name"]]['fieldset'] = $row['fieldset'];
			}
		}
	}


	if ($include_id == false) {
		unset($cols["id"]);
	}

	return $cols;
}

/*
 * Attach data-type to each field
 * 
 */

function get_field_types($table) {
	global $db;


	$fields_meta = explode("\r\n", current($db->query("SELECT TABLE_SOURCE FROM INFORMATION_SCHEMA_TABLES WHERE TABLE_NAME = '$table'")->fetch(PDO::FETCH_ASSOC)));
	unset($fields_meta[0]);

	foreach ($fields_meta as $fm) {
		if (stripos($fm, 'not null') === false) {
			$null = "_null";
		}
		else {
			$null = "";
		}

		preg_match("/\[(.*)\]\s(\w*)/", $fm, $matches);
		$fields_type[$matches[1]] = strtolower($matches[2] . $null);
	}
	$fields_type["id"] = "read-only";




	$query = $db->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA_TABLES");
	while ($res = $query->fetch(PDO::FETCH_ASSOC)) {
		if (in_array($res["TABLE_NAME"] . "_id", array_keys($fields_type))) {

			if (stripos($fields_type[$res["TABLE_NAME"] . "_id"], "_null") === false) {

				$fields_type[$res["TABLE_NAME"] . "_id"] = "drop-down";
			}
			else {
				$fields_type[$res["TABLE_NAME"] . "_id"] = "drop-down_null";
			}
		}
	}


	return $fields_type;
}

/*
 * Check if table estist in $db database
 */

function table_exists($table) {
	global $db;

	return current($db->query("SELECT count(*) FROM INFORMATION_SCHEMA_TABLES WHERE TABLE_TYPE='BASE TABLE' AND TABLE_NAME ='" . $table . "'")->fetch(PDO::FETCH_NUM)) ? true : false;
}

/*
 * Get list of all tables
 */

function all_tables() {
	global $db;

	return selectIntoArray("SELECT TABLE_NAME as 'table' FROM INFORMATION_SCHEMA_TABLES WHERE TABLE_TYPE='BASE TABLE' ORDER BY 'table'", "col");
}

//returns an array of arrays after doing a SELECT
function selectIntoArray($query, $mode="num") {
	global $db;

	$result = $db->query($query);
	if (!$result) //make sure the result is valid
		return false;


	if ($mode == "assoc")
		$mode = PDO::FETCH_ASSOC;
	else if ($mode == "num")
		$mode = PDO::FETCH_NUM;
	else if ($mode == "col")
		$mode = PDO::FETCH_COLUMN;
	else
		$mode = PDO::FETCH_BOTH;
	return $result->fetchAll($mode);
}

function array_searchRecursive($needle, $haystack, $strict=false, $path=array()) {
	if (!is_array($haystack)) {
		return false;
	}

	foreach ($haystack as $key => $val) {
		if (is_array($val) &&
				  $subPath = array_searchRecursive($needle, $val, $strict, $path)) {
			$path = array_merge($path, array($key), $subPath);
			return $path;
		}
		elseif ((!$strict &&
				  $val == $needle) || ($strict &&
				  $val === $needle)) {
			$path[] = $key;
			return $path;
		}
	}
	return false;
}

?>