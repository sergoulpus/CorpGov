<?
/*
 * 
 * Praegu ei näita seoseid mis ei tulene omanikust
 * 
 * 
 */




$seosed = getSeosed($_GET["firma_id"]);

foreach ($seosed as $s) {
	$ids[] = $s["id"];
}
if (!empty($ids))
	foreach (getIsikud($ids) as $id => $isik) {
		$isikud[$isik["id"]] = $isik;
	}
?>
<h1>
	<?
	$firma = current(selectIntoArray("SELECT * FROM firma WHERE id =" . $_GET["firma_id"], "assoc"));
	echo $firma['nimi'];
	?>
</h1>
<? echo "<a  style='float: right' href='$CFG[BASE_URL]/?action=addedit&id=" . $_GET["firma_id"] . "'>MUUDA</a>"; ?>
<div id="schema">



	<fieldset class="box" id="boxOmanikud">
		<legend class="boxTitle">Üldkoosolek</legend>

		<? foreach ($seosed as $i): ?>
			<? if ($i["onOmanik"] == "1"): ?>
				<div class="omanik subbox" id="boxOmanik<?= $i["id"] ?>">
					<? echo renderIsik($isikud[$i["id"]], 'omanik'); $c=true; ?>
				</div>
			<? endif; ?>
		<? endforeach; ?>
		<? if(!isset($c)):?><i>- puudub -</i><? endif; ?>
	</fieldset>
	<? unset($c); ?>

	
	
	<fieldset class="box" id="boxNoukogu">
		<legend class="boxTitle">Nõukogu</legend>

		<? foreach ($seosed as $i): ?>
			<? if ($i["noukoguLiige"] > 0): ?>
				<div class="noukogu subbox" id="boxNoukogu<?= $i["id"] ?>" <? if ($i["seotudOmanik"]) : ?> softCon="boxOmanik<?= $i["seotudOmanik"] ?>" <? endif; ?> <? if ($i["samaOmanik"]) : ?> hardcon="boxOmanik<?= $i["samaOmanik"] ?>" <? endif; ?>>
					<? echo renderIsik($isikud[$i["id"]], 'noukogu');  $c = true; ?>
				</div>
			<? endif; ?>
		<? endforeach; ?>
		<? if(!isset($c)):?><i>- puudub -</i><? endif; ?>
	</fieldset>
	<? unset($c); ?>


	<fieldset class="box" id="boxJuhatus">
		<legend class="boxTitle">Juhatus</legend>

		<? foreach ($seosed as $i): ?>
			<? if ($i["juhatuseLiige"] > 0): ?>
				<div class="juhatus subbox" id="boxjuhatus<?= $i["id"] ?>" <? if ($i["seotudOmanik"]) : ?> softCon="boxOmanik<?= $i["seotudOmanik"] ?>" <? endif; ?> <? if ($i["samaOmanik"]) : ?> hardcon="boxOmanik<?= $i["samaOmanik"] ?>" <? endif; ?>>
					<? echo renderIsik($isikud[$i["id"]], 'juhatus'); $c = true; ?>
				</div>
			<? endif; ?>
		<? endforeach; ?>
		<? if(!isset($c)):?><i>- puudub -</i><? endif; ?>
	</fieldset>
	<? unset($c); ?>


	<fieldset class="box" id="boxOrganisatsioon">
		<legend class="boxTitle">Organisatsioon</legend>

		<? foreach ($seosed as $i): ?>
			<? if (strlen($i["ametinimetus"]) > 0 OR ($i["onOmanik"] != "1" AND $i["noukoguLiige"] <= 0 AND $i["juhatuseLiige"] <= 0)): ?>
				<div class="tootaja subbox" id="boxtootaja<?= $i["id"] ?>" <? if ($i["seotudOmanik"]) : ?> softCon="boxOmanik<?= $i["seotudOmanik"] ?>" <? endif; ?> <? if ($i["samaOmanik"]) : ?> hardcon="boxOmanik<?= $i["samaOmanik"] ?>" <? endif; ?>>
					<? echo renderIsik($isikud[$i["id"]], 'org'); $c= true;?>
				</div>
			<? endif; ?>
		<? endforeach; ?>
		<? if(!isset($c)):?><i>- puudub -</i><? endif; ?>
	</fieldset>
	<? unset($c); ?>



</div>





<?

function getSeosed($firma_id) {
	return selectIntoArray("SELECT * FROM isikudDia WHERE [firma_id] = " . $firma_id . " ORDER BY osalus DESC", "assoc");
}

function getIsikud($ids) {
	if (!is_array($ids))
		$ids = array($ids);
	$sql = "SELECT * FROM isik_mainView WHERE [id] IN(" . implode(",", $ids) . ") ORDER by osalus";
	return selectIntoArray($sql, "assoc");
}

function renderIsik($i, $type='general') {
	global $CFG;
	$skipLabels = array('nimi', 'firma_id', 'id', 'osalus', 'ametinimetus');
	$skipVals = array('- määramata -', '');

	$boolVals = array(
		 '' => '- määramata -',
		 '0' => 'Ei ole',
		 '1' => 'Olemas',
	);

	$isikMeta = get_column_info("isik");


	
	extract($i);
	$fieldsets = array('yld', $type);



	echo "<span class='bName'><a title='Muuda' href='" . $CFG["BASE_URL"] . "/?action=isikCrud&id=$id' target='editwin'>$nimi</a></span>";


	if ($type == 'omanik') {
		echo "<br><span class='bOsalus'>";
		echo $osalus;
		echo "</span>";
	}
	if ($type == 'org') {
		echo "<br><span>";
		echo $ametinimetus;
		echo "</span>";
	}
	
	if(in_array($type, array('juhatus','noukogu','org')))
			  $skipLabels[] = 'juriidilineVormIsik_id';
	
	echo "<ul style='text-align: left;'> ";

	foreach ($i as $l => $d) {
		$show = false;
		foreach ($fieldsets as $fs) {
			if (in_array($fs, $isikMeta[$l]['fieldset']))
				$show = true;
		}
		if ($show == true) {
			if (!in_array($l, $skipLabels)) {
				if (!in_array($d, $skipVals)) {
					if ($isikMeta[$l]['type'] == 'BOOL') {
						$d = $boolVals[$d];
					}
					if (!isset($isikMeta[$l]['display_name'])) {
						echo "Skipping $l - $d<br>";
					}
					else {

						echo'<b>' . $isikMeta[$l]['display_name'] . ':</b> ' . $d . '<br>';
					}
				}
			}
		}
	}

	echo "</ul>";
}
?>