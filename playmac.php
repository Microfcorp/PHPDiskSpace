<?php include_once("/var/www/html/site/secur.php"); ?>
<?
$mac = $_GET['mac'];
$type = $_GET['type'];

if($type == "play"){
//shell_exec("echo -e 'power on\nagent on\nconnect ".$mac." \nquit' | sudo bluetoothctl");

shell_exec('$(sudo bluealsa-aplay --profile-a2dp '.$mac.')');
echo 'ok';
}
elseif($type == "pair"){
	shell_exec("echo -e \"power on\nagent on\npair ".$mac." \nyes\nquit\" | sudo bluetoothctl");
	echo 'ok';
}
elseif($type == "scanefir"){
	echo shell_exec("sudo hcitool scan");
}
elseif($type == "moduleoff"){
//echo shell_exec("sudo /etc/init.d/bluetooth stop");
shell_exec("echo \"power off\nagent off\nquit\" | sudo bluetoothctl");
echo 'ok';

}
elseif($type == "moduleon"){
//echo shell_exec("sudo /etc/init.d/bluetooth start");
shell_exec("echo -e \"power on\nagent on\ndefault-agent\nquit\" | sudo bluetoothctl");
echo 'ok';
}
?>