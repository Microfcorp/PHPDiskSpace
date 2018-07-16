<?
$put = $_GET['mount'];
$st = $_GET['type'];
//echo str_replace('/', '-', $put);

$tmp = "/mnt/".str_replace('/', '-', substr($put, 1));

if($st == 'mount'){
echo mkdir($tmp);
echo shell_exec('sudo mount '.$put. " " .$tmp);
echo 1;
}
elseif($st == 'umount'){
	echo shell_exec('sudo umount '. $put);
	echo 2;
}
elseif($st == 'mountall'){
	echo shell_exec('sudo mount -a');
	echo 2;
}
?>