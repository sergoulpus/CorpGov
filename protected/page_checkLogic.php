<?php

/*
 * 
 * TODO:
 * 		- Vähemalt 1 juhatuse liige
 * 		- Vähemalt 1 omanik
 *
 * 		- üldine kontroll, kas töö sobib valimisse
 * 		- 
 * 
 * 
 */


if ($page == 'checkLogic') {
	runAllChecks();
}

function runAllChecks() {
	ini_set('xdebug.var_display_max_depth', '100');

	$checks = array('checkOsalus', 'checkRoles', 'checkOmanikCount');

	foreach ($checks as $check) {
		echo "<hr>";
		echo "<h3>$check</h3>";
		var_dump($check());
	}
}

/*
 * Kontrolli kas vähemalt üks omanik on
 */

function checkOmanikCount($firma_id = false) {
	$fCon = $firma_id ? 'firma_id = ' . $firma_id : '1';
	$status['is_ok'] = true;

	$sql = '
			SELECT
					f.nimi,      
					f.id,      
					f.juriidilineVormFirma_id as jur,
					(SELECT COUNT(*) from isikudDia where onOmanik = 1 AND isikudDia.firma_id = f.id) as countOmanik,
					(SELECT COUNT(*) from isikudDia where noukoguLiige != 0 AND isikudDia.firma_id = f.id) as countNoukogu,
					(SELECT COUNT(*) from isikudDia where juhatuseLiige != 0 AND isikudDia.firma_id = f.id) as countJuhatus
			FROM firma f

			WHERE
				  ' . $fCon . '
					AND  (countOmanik = 0 OR countJuhatus = 0 OR (jur=2 and countNoukogu=0))
				  
			';


	foreach (selectIntoArray($sql, 'assoc') as $r) {
		$str = array();
		if ($r['countOmanik'] == 0)
			$str[] = 'omanik';
		if ($r['countJuhatus'] == 0)
			$str[] = 'juhatuse liige';
		if ($r['jur'] == 2 and $r['countNoukogu'] == 0)
			$str[] = 'noukogu liige (AS puhul)';


		foreach ($str as $e) {
			$status['is_ok'] = false;
			$status['msg'][] = 'Ettevõttel peab olema vähemalt 1 ' . $e . ': ' . $r['nimi'] . '(' . $r['id'] . ')';
		}
	}
	return $status;
}

/*
 * Kontrollime kas oaslused annavad kokku 100%
 * 
 */

function checkOsalus($firma_id = false) {
	$fCon = $firma_id ? 'firma_id = ' . $firma_id : '1';
	$status['is_ok'] = true;

	$sql = "
		SELECT 
				 [firma].[id] firma_id,
				 [firma].[nimi] firma_nimi,
				 [isik].[id]  isik_id,       
				 [isik].[nimi] isik_nimi,
				 [isik].[osalus]
		FROM [firma]
			  INNER JOIN [isik] ON [isik].[firma_id] = [firma].[id]  
		WHERE 
				" . $fCon . " AND
				LENGTH([isik].[osalus])>0      
		ORDER BY
				firma_id
		";
	$res = selectIntoArray($sql, "assoc");

	$isik_meta = get_column_info("isik");

	foreach ($res as $r) {
		if (!isset($o[$r['firma_id']]['cap']))
			$o[$r['firma_id']]['cap'] = 0;

		if (!preg_match('/' . $isik_meta['osalus']['validation_regexp'] . '/', $r['osalus'])) {
			$status['is_ok'] = false;
			$status['msg'][] = 'Validation error! Firma: ' . $r['firma_nimi'] . '(' . $r['firma_id'] . ')' . ' Isik: ' . $r['isik_nimi'] . '(' . $r['isik_id'] . ')';
		}
		$o[$r['firma_id']]['cap'] += floatval(str_replace(',', '.', $r['osalus']));
		$o[$r['firma_id']]['nimi'] = $r['firma_nimi'];
		//$o[$r['firma_id']]['osanikud'][] = $r;
	}

	foreach ($o as $k => $fo) {
		if ($fo['cap'] != 100) {
			$status['is_ok'] = false;
			$status['msg'][] = 'Osalus not 100%! Firma: ' . $fo['nimi'] . '(' . $k . ')';
		}
	}



	return $status;
}

/*
 * Kontrollime isikuste rollid klapivad
 */

function checkRoles($firma_id = false) {
	$fCon = $firma_id ? 'firma_id = ' . $firma_id : '1';
	$status['is_ok'] = true;

	$sql = "
		SELECT [isikudDia].*, [isikudDia].[nimi] as [isik_nimi], [isikudDia].[id] as [isik_id], [firma].[nimi] as [firma_nimi]
		FROM [isikudDia], [firma]
		WHERE " . $fCon . " AND [isikudDia].[firma_id] = [firma].[id]
		";

	foreach (selectIntoArray($sql, 'assoc') as $r) {

		/* Vähemalt mingi roll peaks olema määratud */
		if ($r['ametinimetus'] == '' AND $r['onOmanik'] == '0' AND $r['juhatuseLiige'] == '0' AND $r['noukoguLiige'] == '0') {
			$status['is_ok'] = false;
			$status['msg'][] = 'No roles set for isik! ' . $r['firma_nimi'] . '(' . $r['firma_id'] . ')' . ' Isik: ' . $r['isik_nimi'] . '(' . $r['isik_id'] . ')';
		}

		/* Isik on ainult töötaja */
		if ($r['ametinimetus'] != '' AND $r['onOmanik'] == '0' AND $r['juhatuseLiige'] == '0' AND $r['noukoguLiige'] == '0') {
			$status['is_ok'] = false;
			$status['msg'][] = 'Isik ei osa valitsemises! ' . $r['firma_nimi'] . '(' . $r['firma_id'] . ')' . ' Isik: ' . $r['isik_nimi'] . '(' . $r['isik_id'] . ')';
		}




		if ($r['juhatuseLiige'] == '1' AND $r['noukoguLiige'] == '1') {
			$status['is_ok'] = false;
			$status['msg'][] = 'Samaaegselt juhatuses ja nõukogus! ' . $r['firma_nimi'] . '(' . $r['firma_id'] . ')' . ' Isik: ' . $r['isik_nimi'] . '(' . $r['isik_id'] . ')';
		}

		if ($r['jur'] == '1' AND ($r['ametinimetus'] != '' OR $r['juhatuseLiige'] == '1' OR $r['noukoguLiige'] == '1')) {
			$status['is_ok'] = false;
			$status['msg'][] = 'Juriidiline isik ei saa olla muud kui omanik! ' . $r['firma_nimi'] . '(' . $r['firma_id'] . ')' . ' Isik: ' . $r['isik_nimi'] . '(' . $r['isik_id'] . ')';
		}
	}
	return $status;
}

?>
