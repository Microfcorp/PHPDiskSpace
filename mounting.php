<?php include_once("/var/www/html/site/secur.php"); ?>
<?
$put = $_GET['mount'];

if($st == 'umount'){
	echo shell_exec('sudo umount '. $put);
}
elseif($st == 'mountall'){
	echo shell_exec('sudo mount -a');
}
?>