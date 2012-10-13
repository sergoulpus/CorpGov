<?
include(__DIR__ . '/protected/config.php');


if (!isset($_GET['action'])) {
	$_GET['action'] = "";
}
switch ($_GET['action']) {
	case 'addedit':
		$page = "addedit";
		break;
	case 'settings':
		$page = "settings";
		break;

	case 'delete_firma':
		delete_firma($_GET["firma_id"]);
		$page = 'browse';
		break;

	case 'isikCrud':
		$page = 'isikCrud';
		break;

	case 'checkLogic':
		$page = 'checkLogic';
		break;

	case 'delete_isik':
		delete_isik($_GET["isik_id"]);
		if (isset($_GET['oldAction'])) {
			$page = $_GET['oldAction'];
		}
		else {
			$page = 'addedit';
		}
		$_GET["id"] = $_GET["firma_id"];
		break;


	case 'visualize':
		$page = "visualize";
		break;

	case 'run_queries':
		break;
	case 'delete':
		break;


	default:
		$page = "browse";
}
// Base template follows


if (isset($_GET["firma_id"]))
	$firma_id_para = '&amp;firma_id=' . $_GET["firma_id"];
elseif ($page == 'isikCrud' AND !isset($_GET['id'])) {
	$firma_id_para = '';
}
elseif ($page == 'isikCrud' AND $_GET['id'] == 'null') {
	$firma_id_para = '&amp;firma_id=' . $_POST['f']['isik']['null']['firma_id'];
}
else
	$firma_id_para = '';
?>
<!DOCTYPE html>
<html>
	<head>

		<title>CorpGov andmebaas</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<!--		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/jquery-ui.min.js"></script>-->

		<script type="text/javascript" src="<?= $CFG["BASE_URL"] ?>/public/jquery.min.js"></script>
		<script type="text/javascript" src="<?= $CFG["BASE_URL"] ?>/public/jquery-ui.min.js"></script>
		<script type="text/javascript" src="<?= $CFG["BASE_URL"] ?>/public/jquery.jsPlumb-1.3.15-all-min.js"></script>		
		<script type="text/javascript" src="<?= $CFG["BASE_URL"] ?>/public/jquery.uitablefilter.js"></script>		



		<!---// load the jNotify CSS stylesheet //--->
		<link type="text/css" href="<?= $CFG["BASE_URL"] . "/public/jquery.jnotify.css" ?>" rel="stylesheet" media="all" />
		<script type="text/javascript" src="<?= $CFG["BASE_URL"] . "/public/jquery.jnotify.js" ?>"></script>

		<!---// custom jquery //--->
		<script type="text/javascript" src="<?= $CFG["BASE_URL"] . "/public/corpgov.js" ?>"></script>


		<!--// custom css -->
		<link type="text/css" href="<?= $CFG["BASE_URL"] . "/public/corpgov.css" ?>" rel="stylesheet" media="all" />


		<!--// page specific js and css -->
<? if (file_exists($CFG["BASE_DIR"] . "/public/custom_$page.css")) { ?>
			<link type="text/css" href="<?= $CFG["BASE_URI"] . "/public/custom_$page.css" ?>" rel="stylesheet" media="all" />


<? } ?>
		<? if (file_exists($CFG["BASE_DIR"] . "/public/custom_$page.js")) { ?>
			<script type="text/javascript" src="<?= $CFG["BASE_URI"] . "/public/custom_$page.js" ?>"></script>		
		<? } ?>



<? //}  ?>




	</head>
	<body>
		<a href="<?= $CFG["BASE_URL"] ?>">Nimekiri</a> |
	<disabled a href="<?= $CFG["BASE_URL"] ?>">Standardpäringud</a> |
	<disabled a href="<?= $CFG["BASE_URL"] ?>">Eripäringud</a> |
		<a href="<?= $CFG["BASE_URL"] ?>/?action=addedit">+ Lisa uus EV</a>
		<a href="<?= $CFG["BASE_URL"] ?>/?action=isikCrud<?= $firma_id_para ?>">+ Lisa uus ISIK</a>
		<a href="<?=$CFG["BASE_URL"]  ?>/?action=visualize&<?= $firma_id_para ?>" >SKEEM</a>		
		
		
		<div  style="display: block; float: right;">
			<a  href="<?= $CFG["BASE_URL"] ?>/?action=settings">Settings</a>
			<a  href="<?= $CFG["BASE_URL"] ?>/?action=checkLogic">Check Logic</a>
		</div>
		<hr>

<? include($CFG["BASE_DIR"] . "/protected/page_$page" . ".php"); ?>




		</body>
		</html>


<?

function delete_firma($id) {
	global $db;


	$sql1 = "DELETE FROM isik WHERE firma_id = $id";
	$sql2 = "DELETE FROM firma WHERE id = $id";


	$db->beginTransaction();
	$r1 = $db->exec($sql1);
	$r2 = $db->exec($sql2);
	$db->commit();

	echo "Kustutatud $r2 firmat ja $r1 seotud isikut!<br> ";
}

function delete_isik($id) {
	global $db;

	$sql = "DELETE FROM isik WHERE id = $id";
	echo "Kustutatud isikuid: " . $db->exec($sql);
	echo "<p>";
}
?>

