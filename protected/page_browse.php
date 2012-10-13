<?php

function myfunc($val, $dummy, $meta) {
	echo "<th>" . $meta[$val]["display_name"] . "</th>";
}

$result = selectIntoArray("SELECT * FROM firma_mainView ORDER BY nimi", "assoc");
if ($result == false) { // 0 vastus päringule
	echo "Andmebaas on tühi";
}
else {
	if(!isset($_GET['filter'])){
		$_GET['filter'] = '';
	}
	
	?>
	<form style="border: 0px solid green; display: inline; width: 300px;" id="filter-form">
		Filter: <input name="filter" id="filter" value="<?= $_GET["filter"] ?>" maxlength="30" size="30" type="text" style='width: 400px;'>
	</form>
	<a id="clear" href="">[ X ]</a>
	<table id="firmaMainView">

		<?
		$c = 0;


		$meta = get_column_info("firma");

		foreach ($result as $r) {

			if ($c++ == 0) {  // first row, create also header row
				echo "\t<tr>";
				echo "<th colspan='2'>Tegevused</th>";
				array_walk(array_keys($r), create_function('$val, $dummy, $meta', 'echo "<th>".$meta[$val]["display_name"]."</th>";'), $meta);
				echo "</tr>\n";
			}


			echo "\t<tr>";
			/* Action buttons */
			echo "<td><a href='$CFG[BASE_URL]/?action=addedit&id=$r[id]'>MUUDA</a></td>";
			echo "<td><a href='$CFG[BASE_URL]/?action=visualize&firma_id=$r[id]' >SKEEM</a></td>";


			array_walk($r, create_function('&$val', 'echo "<td>". $val. "</td>";'));
			echo "</tr>\n";
		}
		?>
	</table>
	<?
}

$stats = selectIntoArray("SELECT status.[text] as status, count(firma.status_id) as sum from firma , status WHERE firma.status_id = status.id group by firma.status_id", 'assoc');
echo "<br><br>";
?>
		<table id="stats">

		<?
		$c = 0;



		foreach ($stats as $r) {

			if ($c++ == 0) {  // first row, create also header row
				echo "\t<tr>";
				array_walk(array_keys($r), create_function('$val, $dummy, $meta', 'echo "<th>".$val."</th>";'), $meta);
				echo "</tr>\n";
			}

			echo "\t<tr>";


			array_walk($r, create_function('&$val', 'echo "<td>". $val. "</td>";'));
			echo "</tr>\n";
		}
		?>
	</table>

<?


echo "\n\n";
?>