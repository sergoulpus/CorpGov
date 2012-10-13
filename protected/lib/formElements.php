<?

function make_inputelement($type, $name, $arrayname = "f", $value='', $notnull=false) {
	switch ($type) {
		case "AUTO":
			if ($value == null) {
				echo "-auto-";
			}
			echo "$value<input type='hidden' name='" . $arrayname . "[" . $name . "]' value='$value'>";
			break;
		case "DROP-DOWN":
			make_dropdown($name, $arrayname, $value, $notnull);
			break;
		case "BOOL":
			make_boolean($name, $arrayname, $value);
			break;
		default:
			echo "<input type='text' name='" . $arrayname . "[" . $name . "]' value='$value'>";
	}
	echo $notnull ? "*" : "";
}

function make_dropdown($name, $arrayname='f', $value='', $notnull = true) {
	global $db;

	$table = substr($name, 0, -3);
	$value = $value == null ? 0 : $value;

	if ($table == "isik") {
		$query = "SELECT id, nimi as text FROM isik ORDER by text";
	}
	elseif ($table == "seotudOmanik") {
		$query = "
				SELECT 
					[id], 
					[nimi] || ' (' || [registrikood] ||')' as [text],
					case when [firma_id] = " . $value . "  
						then 0 
						else [firma_id] 
					end as [sort_order]
				FROM [isik]
				WHERE [juriidilineVormIsik_id] = 1
				ORDER BY [sort_order], [text]
		";
//TODO: kontrolli kas töötab
	}
	elseif ($table == "firma") {
		$query = "
				SELECT 
					[id], 
					[nimi] || ' (' || [registrikood] ||')' as [text]
				FROM [firma]
				ORDER BY [text]
		";
	}
	else {
		$query = "SELECT id, text FROM '$table' ORDER by id";
	}


	$res = selectIntoArray($query, "assoc");

	echo "<select name='" . $arrayname . "[" . $name . "]'>";

	if ($notnull == false) {
		echo '<option value="">- määramata -</option>';
	}

	foreach ($res as $option => $r) {
		if ($r["id"] == $value)
			$checked = "selected='1'";
		else
			$checked = '';

		echo '<option value="' . $r["id"] . '" ' . $checked . '>' . $r["text"] . '</option>';
	}
	echo "</select>";
}

function make_boolean($name, $arrayname='f', $value='', $notnull = false) {

	//var_dump($name,$arrayname,$value,$notnull);
	
	
	if($value == ''){
		$value = false;
	}
	
	if ($notnull == false) {
		$opts = array(
			 "- määramata -" => false,
			 "Olemas" => '1',
			 "Ei ole" => '0'
		);
	}
	else {
		$opts = array(
			 "Olemas" => '1',
			 "Ei ole" => '0'
		);
	}

	foreach ($opts as $n => $v) {

		if ($v === $value)
			$checked = "checked='1'";
		else
			$checked = '';

		echo "<nobr><input type='radio' name='" . $arrayname . "[" . $name . "]' value='$v' id='" . $arrayname . $n . $name . "' $checked>";
		echo "<label for='" . $arrayname . $n . $name . "' >$n</label></nobr>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	}
}

?>