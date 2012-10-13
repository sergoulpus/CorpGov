<?
$a = 0;


foreach(a() as $b) {
	echo "Tsükkel!<br>";
}


function a (){
	global $a;
	$a++;
	echo $a;
	
	return array('1','2','3');
}

?>

