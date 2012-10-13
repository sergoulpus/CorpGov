<?
if (isset($_POST["f"])) {
	$form_data = $_POST["f"];
	save_form($form_data);
}
show_form();
exit;

function show_form() {
	global $db, $CFG;

	$tables = array("firma", "isik");
	?>
	<FORM name="settings" action="<?= $CFG["BASE_URL"] ?>/?action=settings" METHOD="POST">
		<table border="1" width="100%">
			<?
			$r = selectIntoArray("PRAGMA table_info('metainfo')", "assoc");
			unset($r[0]);
			echo "<tr>";
			$i = 0;
			foreach ($r as $c => $col) {
				echo "<th>" . $col["name"] . "</th>";
			}
			echo "</tr>";


			foreach ($tables as $table) {
				$metainfo = get_column_info($table, false, false);


				foreach ($metainfo as $field) {
					$field["table_name"] = $table;
					$field["column_name"] = $field["name"];
					
					$field["params"] = implode(",", $field["params"]);
					$field["fieldset"] = implode(",", $field["fieldset"]);
					?>
					<tr>
						<?
						foreach ($r as $c => $col) {
							
							
							
							?>
							<td>
								<? if (!in_array($col["name"], array("table_name", "column_name"))) { ?>
									<input style="width: 99%" type="text" name="f[<?= $i ?>][<?= $col["name"] ?>]" value="<?= $field[$col["name"]] ?>">
									<?
								}
								else {
									echo $field[$col["name"]];
									?>
									<input type="hidden" name="f[<?= $i ?>][<?= $col["name"] ?>]" value="<?= $field[$col["name"]] ?>">
									<?
								}
								?>
							</td>
								<?
							}
							?>

					</tr>
			<?
			$i++;
		}
	}
	?>

		</table>
		<INPUT TYPE="submit" NAME="submit" value="Salvesta"></form>
	<?
}

function save_form($f) {
	global $db;


	$mc = get_column_info("metainfo", true, false);

	$sql = 'INSERT OR REPLACE INTO metainfo  ([' . implode('], [', $mc) . ']) ';
	$sql .='VALUES (' . ':' . implode(', :', $mc) . ' )';


	$query = $db->prepare($sql);

	



	$db->beginTransaction();

	$db->exec("DELETE FROM metainfo");
	foreach ($f as $num => $row) {
		foreach ($row as $name => $val) {
			$sql_array[":" . $name] = $val;
		}
		$qr = $query->execute($sql_array);
	}

	$db->commit();
}
?>