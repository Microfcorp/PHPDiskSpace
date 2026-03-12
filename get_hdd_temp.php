<?php include_once("/var/www/html/site/secur.php"); ?>
<?php
	function getTemp($disk){
		$temp = shell_exec("sudo hddtemp $disk 2>&1");
		return trim(explode(':', $temp)[2]);
	}
	function P($name, $type = "string"){
		$pr = (isset($_POST[$name]) ? $_POST[$name] : (isset($_GET[$name]) ? $_GET[$name] : NULL));
		
		if($type == "string")
			return $pr;
		elseif($type == "int" && $pr != NULL)
			return intval($pr);
		elseif($type == "float" && $pr != NULL)
			return floatval($pr);
		else return $pr;
	}
	function validateDevicePath($path) {
		return preg_match('/^\/dev\/[a-zA-Z]+(\d+)?$/', $path) === 1;
	}
	
	$diskName = P("hdd");
	$diskTmp = "";
	
	$diskArray = explode(';', $diskName);
	for($i = 0; $i < count($diskArray); $i++){
		$diskOne = $diskArray[$i];
		
		if($diskOne != "" && validateDevicePath($diskOne))
			$diskTmp = $diskTmp . getTemp($diskOne) . ";";
	}	
	
	echo $diskTmp;
?>