<h2>Lisa/muuda isikut</h2> 
<?
/* identify, if we are saving data or just opening form */

if (isset($_POST["f"])) {
	$form_data = $_POST["f"];
	$status = save_data($form_data);

	if ($status["status"] == false) {
		echo "<div id='statusMsg' type='error' style='display: none'>Error data not saved - see below</div>";
		//var_dump($status);
		show_form($form_data, $status);
	}
	else {
		$_GET["id"] = $status["isik_id"];
		echo "<div id='statusMsg' type='success' style='display: none'>Success, data saved</div>";
		show_form();
	}
}
// adding new, show empty form
else {
	show_form();
}
exit;

function show_form($form = null, $status=false) {
	global $CFG, $db, $_GET;

	if (isset($_GET['firma_id']))
		$overrides = array('firma_id' => $_GET['firma_id']);
	else
		$overrides = array();

	$form = load_data($form, $overrides);


	$isik_meta = get_column_info("isik");
	$fieldsets = array("yld" => "Üldinfo", "omanik" => "Omanik", "noukogu" => "Nõukogu", "juhatus" => "Juhatus");

	$isik_id = $form['isik']['id']['value'];
	$isik = $form['isik'];
	?>
	<FORM name="addedit" action="<?= $CFG["BASE_URL"] ?>/?action=isikCrud&amp;id=<?= $form['isik']["id"]["value"] ?>" METHOD="POST">
		<table>
			<tr>
				<? // foreach ($form["isik"] as $isik_id => $isik) {  ?>
				<td id="isik_col_<?= $isik_id ?>" valign="top" class="isik_column">
					<span id="isik_header_<?= $isik_id ?>"><b>Isik <?= $isik_id == "null" ? "(uus)" : $isik_id ?></b></span>
					<? if ($isik_id != "null") : ?>
						<span style="float: right;">
							<a href="<?= $CFG["BASE_URL"] ?>/?action=delete_isik&isik_id=<?= $isik_id ?>&oldAction=isikCrud&firma_id=<?= $form["isik"]["firma_id"]["value"] ?>">Kustuta see isik</a>
						</span>
					<? endif; ?>



					<? foreach ($fieldsets as $fieldsetkey => $fieldsetname) { ?>
						<fieldset class="isik_column <?= $fieldsetkey ?>"><legend><?= $fieldsetname ?></legend><table style="border-width: 0px !important; ">
								<? foreach ($isik as $name => $element) { ?>

									<? if (in_array($fieldsetkey, $element["fieldset"]) OR ($fieldsetkey == "yld" AND empty($element["fieldset"]) )) { ?>

										<tr style="border-width: 0px !important; ">
											<td style="border-width: 0px !important;"  title="<?= $element["tooltip"] ?>"><?= $element["display_name"] ?></td>
											<td style="border-width: 0px !important; ">
												<?
												if ($name == "seotudOmanik_id") {
													$element["type"] = "DROP-DOWN";
												}
												if ($name == "firma_id") {
													$element["type"] = "DROP-DOWN";
												}

												make_inputelement($element["type"], $name, 'f[isik][' . $isik_id . ']', $element["value"], $element["notnull"]);

												if (isset($status["errors"]["isik"])) {
													$err = array_searchRecursive($name, $status["errors"]["isik"]);
													if ($err != false AND $status["errors"]["isik"][$err[0]]["id"] == $isik_id) {
														echo "<span class=error>" . $status["errors"]["isik"][$err[0]]["txt"] . "</span>";
//																		var_dump($err, $status);
													}
												}
												?>
											</td>
										</tr>



									<? } ?>



								<? } ?>
							</table></fieldset>
					<? } ?>
				</td>
				<? // }  ?>
			</tr>
			<tr>
				<td colspan="2">
					<INPUT TYPE="submit" NAME="submit" value="Salvesta">	
				</td>
			</tr>
		</table>

		<p><?= str_repeat('&nbsp;', 100) ?><a href="<?= $CFG["BASE_URL"] ?>/?action=delete_isik&id=<?= $form["isik"]["id"]["value"] ?>">Kustuta see isik</a></p>
	</form>
	<?
}

function load_data($post_data = null, $override=array()) {
	global $db, $_GET;

	$post = $post_data == null ? false : true;

	if (!isset($_GET["id"])) {
		$id = null;
	}
	else {
		$id = $_GET["id"];
	}

	// Get isikud values either from POST variable, database or defaults

	/* Set default values for all in isik_meta */
	$isik_meta = get_column_info("isik");  // meta info

	foreach ($isik_meta as $field => $val) {
		if (in_array($field, array_keys($override))) {
			$isik_meta[$field]['default_value'] = $override[$field];
		}



		$isik_meta[$field]["value"] = $isik_meta[$field]["default_value"];
	}

	$isik_meta['id']['value'] = 'null';

	if ($post != false) {
		//echo "There was error saving data - this is isik data from POST variable<br>";
		$isik = $post_data["isik"];
	}
	else {
		//echo "This is from database isik <br>";
		$isik = selectIntoArray("SELECT * FROM isik WHERE id = '$id'", "assoc");
	}



	$tisik = $isik_meta;
	if ($isik != false)
		foreach (current($isik) as $field => $value) {
			$tisik[$field]["value"] = $value;
		}


	$form["isik"] = $tisik;

	return $form;
}

function save_data($form_data) {
	global $db;


	$status ["status"] = true;

	$isik_meta = get_column_info("isik");


	// Valideerime isiku

	foreach ($form_data["isik"] as $ik => $iv) {



		foreach ($isik_meta as $field => $ds) {
			if ($ds["type"] != "AUTO") {
				if ($ds["notnull"] == true AND $ds["type"] == "TEXT" AND strlen($iv[$field]) < 1) {
					$status["errors"]["isik"][] = array("txt" => "Väli '" . $ds["display_name"] . "' $ik. isikul on kohustuslik täita!", "field" => $field, "id" => $ik);
					$status["status"] = false;
				}
				elseif ($ds["notnull"] == true AND $iv[$field] === false) {
					$status["errors"]["isik"][] = array("txt" => "Väli '" . $ds["display_name"] . "' $ik. isikul on kohustuslik täita!", "field" => $field, "id" => $ik);
					$status["status"] = false;
				}


				// Validate regexpi vastu, kui see on olemas
				if ($ds["type"] == "TEXT" AND strlen($iv[$field]) > 0 AND !empty($ds["validation_regexp"])) {
					if (!preg_match("/$ds[validation_regexp]/", $iv[$field])) {
						$status["errors"]["isik"][] = array("txt" => "Väli '" . $ds["display_name"] . "' $ik. isikul ei vasta standardile ($ds[validation_regexp])!", "field" => $field, "id" => $ik);
						$status["status"] = false;
					}
				}
			}
		}
	}





	/* Everything OK, save data */
	if ($status["status"] == true) {
		/* Everything OK, save data */
		$isik_columns = get_column_info("isik", true);


		/* Prepeare sql */
		$sql = 'INSERT OR REPLACE INTO isik  (' . implode(', ', $isik_columns) . ') ';
		$sql .='VALUES (' . ':' . implode(', :', $isik_columns) . ' )';

		$query_isik = $db->prepare($sql);






		/* NOW INSERT ALL isikud */
		foreach ($form_data["isik"] as $key => $isik) {
			if ($key == "null")
				$form_data["isik"]["null"]["id"] = null;
			unset($sql_array);
			foreach ($isik_columns as $name) {
				$sql_array[":" . $name] = $form_data["isik"][$key][$name];
			}
			$result_isik = $query_isik->execute($sql_array);
			$status["isik_id"] = $db->lastInsertId();
		}
	}

	return $status;
}
?>



