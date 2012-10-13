<h2>Lisa/muuda</h2>
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
		echo "<div id='statusMsg' type='success' style='display: none'>Success, data saved</div>";
		$_GET["id"] = $status["firma_id"];
		show_form();
	}
}
// adding new, show empty form
else {
	show_form();
}
exit;

function show_form($form = null, $status=false) {
	global $CFG, $db;
	$form = load_data($form);

	$firma_meta = get_column_info("firma");

//var_dump($form);
	?>
	<FORM name="addedit" action="<?= $CFG["BASE_URL"] ?>/?action=addedit&id=<?= $form["firma"]["id"]["value"] ?>" METHOD="POST">
		<table border="1">
			<tr>
				<td>
					<h3>Äriühing</h3>
					<? echo "<a href='$CFG[BASE_URL]/?action=visualize&firma_id=" . $form["firma"]["id"]["value"] . "'>SKEEM</a>"; ?>
					<table>
						<?
						foreach ($form["firma"] as $name => $element) {
							echo "<tr><td title='" . $firma_meta[$name]["tooltip"] . "'>" . $firma_meta[$name]["display_name"] . "</td>";
							echo "<td>";
							make_inputelement($element["type"], $name, "f[firma]", $element["value"], $element["notnull"]);
							if (isset($status["errors"]["firma"])) {
								$err = array_searchRecursive($name, $status["errors"]["firma"]);
								if ($err != false) {
									echo "<span class=error>" . $status["errors"]["firma"][$err[0]]["txt"] . "</span>";
								}
							}
							echo "</td></tr>";
						}
						?>


						<tr>
							<td colspan="2">
								<INPUT TYPE="submit" NAME="submit" value="Salvesta">
							</td>
						</tr>
					</table>
					</form>
					<p><a href="<?= $CFG["BASE_URL"] ?>/?action=delete_firma&firma_id=<?= $form["firma"]["id"]["value"] ?>">Kustuta see ettevõte</a></p>
				</td></tr><tr><td>

					<h3>Seotud isikud</h3>
					<?
					$fieldsets = array("yld" => "Üldinfo", "omanik" => "Omanik", "noukogu" => "Nõukogu", "juhatus" => "Juhatus");
					$colCount = count($form['isik']) + 1;
					$colCount += floor($colCount / 6);
					$curCol = 0;
					$rowMeta = array_shift(array_values($form['isik']));
					?>

					<table id="isikudTable">
						<!-- header row start -->
						<tr>

							<? foreach ($form["isik"] as $isik_id => $isik) : ?>
								<? if ($curCol == 0 OR $curCol % 6 == 0) : $curCol++; ?><th>Väli</th> <? endif; ?>
								<th class="isikFormHeader">
									<span style="margin-right: 10px;" id="isik_header_<?= $isik_id ?>"><b>Isik <?= $isik_id == "null" ? "(uus)" : $isik_id ?></b></span>
									<? if ($isik_id != "null") : ?>
										<span>
											<a href="<?= $CFG["BASE_URL"] ?>/?action=delete_isik&isik_id=<?= $isik_id ?>&oldAction=addedit&firma_id=<?= $form["firma"]["id"]["value"] ?>">[ X ]</a>
										</span>
									<? endif; ?>
								</th>
								<? $curCol++; ?>
							<? endforeach; ?>
						</tr>
						<!-- header row end -->

						<? $curCol = 0; ?>

						<? foreach ($fieldsets as $fieldsetkey => $fieldsetname) : ?>
							<tr><td colspan="<?= $colCount ?>"></td></tr><!-- empty row before section header -->
							<tr><td colspan="<?= $colCount ?>" class="sectionHeaderRow"><?= $fieldsetname ?></td></tr><!-- section header -->

							<? foreach ($rowMeta as $rmK => $rmV) : ?>
								<? if (in_array($fieldsetkey, $rmV["fieldset"]) OR ($fieldsetkey == "yld" AND empty($rmV["fieldset"]) )) : ?>							
									<tr>

										<? foreach ($form["isik"] as $isik_id => $isik) : ?>

											<? if ($curCol == 0 OR $curCol % 6 == 0) : $curCol++; ?>
												<td class="firstColCell"  title="<?= $rmV["tooltip"] ?>"><?= $rmV["display_name"] ?></td><!-- first column headers -->
											<? endif; ?>
											<td class="inputElementCell fs<?= $fieldsetkey ?> fisik<?= $isik_id ?>">
												<!-- input element cell contents boundary ---------------------------------------------------------------------->
												<?
												$curCol++;
												$name = $rmK;
												$element = $isik[$rmK];

												if ($name == "seotudOmanik_id") {
													$element["type"] = "DROP-DOWN";
												}
												make_inputelement($element["type"], $name, 'f[isik][' . $isik_id . ']', $element["value"], $element["notnull"]);

												if (isset($status["errors"]["isik"])) {
													foreach ($status["errors"]["isik"] as $err) {
														if ($err['field'] == $name AND $err['id'] == $isik_id) {
															echo "<span class=error>" . $err["txt"] . "</span>";
															break;
														}
													}
												}
												?>



												<!-- input element cell contents boundary ---------------------------------------------------------------------->
											</td>
										<? endforeach; ?>
									</tr>
									<? $curCol = 0; ?>
								<? endif; ?>
							<? endforeach; ?>
						<? endforeach; ?>
					</table>



					<? return; ?>








					<table> <!-- vertical columns per isik -->
						<tr>
							<? foreach ($form["isik"] as $isik_id => $isik) { ?>
								<td id="isik_col_<?= $isik_id ?>" valign="top" class="isik_column">
									<span id="isik_header_<?= $isik_id ?>"><b>Isik <?= $isik_id == "null" ? "(uus)" : $isik_id ?></b></span>


									<? if ($isik_id != "null") : ?>
										<span style="float: right;">
											<a href="<?= $CFG["BASE_URL"] ?>/?action=delete_isik&isik_id=<?= $isik_id ?>&oldAction=addedit&firma_id=<?= $form["firma"]["id"]["value"] ?>">Kustuta see isik</a>
										</span>
									<? endif; ?>
									<div style="visibility: hidden;"><? echo str_repeat('a', 40) ?></div>
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
							<? } ?>
						</tr>
					</table>




					<?
				}

				function load_data($post_data = null) {
					global $db, $_GET;

					$post = $post_data == null ? false : true;

					if (!isset($_GET["id"])) {
						$id = null;
					}
					else {
						$id = $_GET["id"];
					}


					/*
					 * $id - firma id firma tabelis
					 * $post on kas kogu saadetud vorm või false, kui on uus firma
					 */



					// Get firma values either from POST variable, database or defaults

					if ($post != false) {
						//echo "There was error saving data - this is firma data from POST variable<br>";
						$firma_data[0] = $post_data["firma"];
					}
					else {
						//echo "This is from database firma data<br>";
						$firma_data = selectIntoArray("SELECT * FROM firma WHERE id = $id", "assoc");
					}

					/* GENERATE $form variable with default values if needed */
					$fields["firma"] = get_column_info("firma");
					foreach ($fields["firma"] as $field => $info) {
						if ($firma_data != false && isset($firma_data[0][$field])) {
							$fields["firma"][$field]["value"] = $firma_data[0][$field];
						}
						else {
							$fields["firma"][$field]["value"] = $fields["firma"][$field]["default_value"];
						}
					}
					$form = $fields;


					// Get isikud values either from POST variable, database or defaults
					// Add empty data set in the end

					/* Set default values for all in isik_meta */
					$isik_meta = get_column_info("isik");  // meta info
					foreach ($isik_meta as $field => $val) {
						$isik_meta[$field]["value"] = $isik_meta[$field]["default_value"];
					}
					unset($isik_meta["firma_id"]);

					if ($post != false) {
						//echo "There was error saving data - this is isik data from POST variable<br>";
						$isikud = $post_data["isik"];
					}
					else {
						//echo "This is from database isik <br>";
						$isikud = selectIntoArray("SELECT * FROM isik WHERE firma_id = '$id'", "assoc");
					}


					foreach ($isikud as $num => $isik) {
						$tisik[$isik["id"]] = $isik_meta;
						foreach ($isik as $field => $value) {
							if ($field != "firma_id")
								$tisik[$isik["id"]][$field]["value"] = $value;
						}
					}
					if (empty($tisik["null"]) && $post == false) {
						$tisik["null"] = $isik_meta;  // add empty dataset in the end if needed
					}
					elseif ($post != false) {
						$tisik["null"] = $tisik[""];
						unset($tisik[""]);
					}
					$form["isik"] = $tisik;




					return $form;
				}

				function save_data($form_data) {
					global $db;

					$status ["status"] = true;
//		$firma_types = get_field_types("firma");
//		$isik_types = get_field_types("isik");


					$firma_meta = get_column_info("firma");
					$isik_meta = get_column_info("isik");


					/* Valideerime vormi */


					/* Kontrollime, kas kohustuslikud firma väljad on täidetud ja vastavad regexpile */
					foreach ($firma_meta as $field => $ds) {
						if ($ds["type"] != "AUTO") {
							if ($ds["notnull"] == true AND $ds["type"] == "TEXT" AND strlen($form_data["firma"][$field]) < 1) {
								$status["errors"]["firma"][] = array("txt" => "Väli '" . $ds["display_name"] . "' on kohustuslik täita!", "field" => $field);
								$status["status"] = false;
							}
							elseif ($ds["notnull"] == true AND $form_data["firma"][$field] === false) {
								$status["errors"]["firma"][] = array("txt" => "Väli '" . $ds["display_name"] . "' on kohustuslik täita!", "field" => $field);
								$status["status"] = false;
							}


							// Validate regexpi vastu, kui see on olemas
							if ($ds["type"] == "TEXT" AND strlen($form_data["firma"][$field]) > 0 AND !empty($ds["validation_regexp"])) {
								if (!preg_match("/$ds[validation_regexp]/", $form_data["firma"][$field])) {
									$status["errors"]["firma"][] = array("txt" => "Väli '" . $ds["display_name"] . "' ei vasta standardile ($ds[validation_regexp])!", "field" => $field);
									$status["status"] = false;
								}
							}
						}
					}


					/* Kontrollime, kas uue isiku vorm on täidetud ja see on vaja salvestada */
					/*
					 * Kontrollida kas mõni uue isiku vormi välja on defaultist erinev või
					 * mõni kohustuslik väli on täidetud
					 */
					$isik_new = false;

					$isik = $form_data["isik"]["null"];
//		var_dump($isik);
					foreach ($isik_meta as $field => $ds) {
						if ($field != "firma_id") {
							if ($ds["type"] == "TEXT" AND strlen($isik[$field]) > 0) {
								$isik_new = true;
								$form_data["isik"]["null"]["id"] = null;
								break;
							}
							if ($ds["default_value"] != $isik[$field]) {
								//var_dump($ds["default_value"] , $isik[$field],$field);
								$isik_new = true;
								$form_data["isik"]["null"]["id"] = null;
								break;
							}
						}
					}

//		var_dump($isik_new);

					if ($isik_new == false) {
						unset($form_data["isik"]["null"]);
					}


					// Valideerime isikud
					$c = 0;
					foreach ($form_data["isik"] as $ik => $iv) {
						$c++;

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


						$firma_columns = get_column_info("firma", true);
						$isik_columns = get_column_info("isik", true);
						foreach ($firma_columns as $name) {
							if (!isset($form_data["firma"][$name]))
								$form_data["firma"][$name] = '';

							if ($name == 'id' && $form_data["firma"][$name] == '')
								$form_data["firma"][$name] = null;

							$sql_array[":" . $name] = $form_data["firma"][$name];
						}


						$sql = 'INSERT OR REPLACE INTO firma  (' . implode(', ', $firma_columns) . ') ';
						$sql .='VALUES (' . ':' . implode(', :', $firma_columns) . ' )';
						$query_firma = $db->prepare($sql);


						/* Prepeare sql */
						$sql = 'INSERT OR REPLACE INTO isik  (' . implode(', ', $isik_columns) . ') ';
						$sql .='VALUES (' . ':' . implode(', :', $isik_columns) . ' )';

						$query_isik = $db->prepare($sql);


						$db->beginTransaction();

						$result_firma = $query_firma->execute($sql_array);
						$status["firma_id"] = $firma_id = $db->lastInsertId();

						/* NOW INSERT ALL isikud */
						foreach ($form_data["isik"] as $key => $isik) {
							if ($isik["id"] != "null") {
								unset($sql_array);
								$form_data["isik"][$key]["firma_id"] = $firma_id;
								foreach ($isik_columns as $name) {
									$sql_array[":" . $name] = $form_data["isik"][$key][$name];
								}
								$result_isik = $query_isik->execute($sql_array);
							}
						}
						// end transaction
						$db->commit();
					}


					return $status;
				}

				/*				 *
				 *
				 * Moved To formElements.php
				 * 
				 * 

				  function xxxmake_inputelement($type, $name, $arrayname = "f", $value='', $notnull=false) {


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
				  function xxxmake_dropdown($name, $arrayname='f', $value='', $notnull = true) {
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
				  case when [firma_id] = " . "18" . "
				  then 0
				  else [firma_id]
				  end as [sort_order]
				  FROM [isik]
				  WHERE [juriidilineVormIsik_id] = 1
				  ORDER BY [sort_order], [text]
				  ";


				  //$query = "SELECT id, nimi || ' (' || [registrikood] ||')' as text FROM isik WHERE [juriidilineVormIsik_id] = 1 ORDER by text";
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
				  function xxxmake_boolean($name, $arrayname='f', $value='', $notnull = false) {


				  if ($notnull == false) {
				  $opts = array(
				  "- määramata -" => null,
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

				  if ($v == $value)
				  $checked = "checked='1'";
				  else
				  $checked = '';

				  echo "<nobr><input type='radio' name='" . $arrayname . "[" . $name . "]' value='$v' id='" . $arrayname . $n . $v . "' $checked>";
				  echo "<label for='" . $arrayname . $n . $v . "' >$n</label></nobr>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				  }
				  }
				 */
				?>



