<?php include_once("/var/www/html/site/secur.php"); ?>
<!Doctype html>
<html>
<head>
<title>10 корпус. Система</title>
<style>
.storage-text {
    font-family: Arial, sans-serif;
    font-size: 14px;
    /* font-weight: bold; */
    padding: 7px 10px;
    border-radius: 8px;
    /*background-color: #eef2f3;*/
    box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1);
    display: inline-block;
  }
  
.mediumFree {
	color: #ca931d;;
}

.smallFree {
	color: red;
}
</style>
<script>
	function createXMLHttp() {
        if (typeof XMLHttpRequest != "undefined") { // для браузеров аля Mozilla
            return new XMLHttpRequest();
        } else if (window.ActiveXObject) { // для Internet Explorer (all versions)
            var aVersions = [
                "MSXML2.XMLHttp.5.0",
                "MSXML2.XMLHttp.4.0",
                "MSXML2.XMLHttp.3.0",
                "MSXML2.XMLHttp",
                "Microsoft.XMLHttp"
            ];
            for (var i = 0; i < aVersions.length; i++) {
                try {
                    var oXmlHttp = new ActiveXObject(aVersions[i]);
                    return oXmlHttp;
                } catch (oError) {}
            }
            throw new Error("Невозможно создать объект XMLHttp.");
        }
    }

// фукнция Автоматической упаковки формы любой сложности
function getRequestBody(oForm) {
    var aParams = new Array();
    for (var i = 0; i < oForm.elements.length; i++) {
        var sParam = encodeURIComponent(oForm.elements[i].name);
        sParam += "=";
        sParam += encodeURIComponent(oForm.elements[i].value);
        aParams.push(sParam);
    }
    return aParams.join("&");
}
// функция Ajax POST
function postAjax(url, oForm, callback) {
    // создаем Объект
    var oXmlHttp = createXMLHttp();
    // получение данных с формы
    var sBody = oForm;
    // подготовка, объявление заголовков
    oXmlHttp.open("POST", url, true);
    oXmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	//oXmlHttp.addHeader("Access-Control-Allow-Origin", "*");
    // описание функции, которая будет вызвана, когда придет ответ от сервера
    oXmlHttp.onreadystatechange = function() {
        if (oXmlHttp.readyState == 4) {
            if (oXmlHttp.status == 200) {
                callback(oXmlHttp.responseText);
            } else {
                callback('error' + oXmlHttp.statusText);
            }
        }
    };
    // отправка запроса, sBody - строка данных с формы
    oXmlHttp.send(sBody);
}
function GetTemp(){
	postAjax('console.php?command=sensors&cd=system', "", function(d){document.getElementById("temp").innerHTML = d;})
	
}
function GetSysLoad(){
	postAjax('get_sys_load.php', "", function(d){document.getElementById("sysLoad").innerHTML = d;})
	
}
function GetHDDTemp(hddName){
	postAjax('get_hdd_temp.php', "hdd="+hddName, function(d){
		const allReturn = d.split(';');
		const allReq = hddName.split(';');
		for(var i = 0; i < allReturn.length; i++){
			if(allReturn[i] != ""){
				document.getElementById("temp_"+allReq[i]).innerHTML = allReturn[i];
			}
		}		
	});
}
function UpdateHDDTemp(){
	const allDisk = document.getElementsByClassName("hddtemp");
	var hddTemp = "";
	for(var i = 0; i < allDisk.length; i++){
		const nameDisk = allDisk[i].id.split('_')[1];
		hddTemp += nameDisk + ";";
	}
	GetHDDTemp(hddTemp);
}
/*function Playmac(mac){
	postAjax('playmac.php?type=play&mac=' + mac, "", function(d){document.getElementById("scan").innerHTML = d;})
	
}
function Pairmac(mac){
	postAjax('playmac.php?type=pair&mac=' + mac, "", function(d){document.getElementById("scan").innerHTML = d;})
	
}
function moduleoff(){
	postAjax('playmac.php?type=moduleoff&mac=null', "", function(d){document.getElementById("scan").innerHTML = d;})
	
}
function moduleon(){
	postAjax('playmac.php?type=moduleon&mac=null', "", function(d){document.getElementById("scan").innerHTML = d;})
	
}
function Scanefir(mac){
	document.getElementById("scan").innerHTML = "Пожалуйста подождите";
	postAjax('playmac.php?type=scanefir&mac=null', "", function(d){document.getElementById("scan").innerHTML = d;})
	
}*/


function umounting(data){
	if(confirm("Вы действительно хотите отмонтировать " + data))
		postAjax('mounting.php?type=umount&mount=' + data, "", function(d){alert(d);})
}

function mountall(data){
	postAjax('mounting.php?type=mountall&mount=' + data, "", function(d){alert(d);})
}
window.setTimeout("UpdateHDDTemp()",1);
window.setTimeout("GetTemp()",100);
window.setTimeout("GetSysLoad()",200);

window.setInterval("UpdateHDDTemp()",10000);
window.setInterval("GetSysLoad()",20000);
window.setInterval("GetTemp()",15000);
</script>
</head>
<body>
  <?php include_once("/var/www/html/site/verh.php"); ?>
  <H1 style="text-align: center; color:red;">Управление системой Debian</H1>
  <div>
  <p style="text-align: center;font-size:20px;">Свободного места на дисках:</p>
  <!-- <button OnClick="mountall(all)">Смонтировать по fstab</button>-->
  <table border="1" width="100%" cellpadding="5">
  <tr>
  <td>
  <p>Точка монтирования - Объект диска - Температура диска</p>
  </td>
  <td>
  <p>Свободно - Всего - Процент занятого</p>
  </td>
  </tr>
<?

	$cont = 0;
	$pizza  = shell_exec("sudo bash devparse.sh 2>&1");
	$pieces = explode("\n", $pizza);
	$allMountPoint = [];
	$mediaDir = '/media';
	$allStorageSize = 0;
	$allFreeStorageSize = 0;
  
	function formatFileSize($size, $rd = 0) {
		$a = array("B", "KB", "MB", "GB", "TB", "PB");
		$pos = 0;
		while ($size >= 1024) {
			$size /= 1024;
			$pos++;
		}
		return round($size,$rd)." ".$a[$pos];
	}
	
	function getDiskUsage($path) {
		// Команда df выводит информацию о файловой системе
		$command = "sudo df -B1 '$path'"; // -B1 для байтов
		$output = shell_exec($command);
		if (!$output) {
			return null;
		}

		// Разобьём вывод на строки
		$lines = explode("\n", trim($output));
		if (count($lines) < 2) {
			return null;
		}

		// Первая строка — заголовок, вторая — данные
		$columns = preg_split('/\s+/', $lines[1]);

		// В стандартном выводе df: 
		// Filesystem  1B-blocks     Used Available Use% Mounted on
		// В массиве:
		// [0] — Filesystem
		// [1] — 1B-блоки (общее пространство)
		// [2] — Used
		// [3] — Available
		// [4] — Use%
		// [5] — Mounted on

		$total = isset($columns[1]) ? floatval($columns[1]) : 0;
		$free = isset($columns[3]) ? floatval($columns[3]) : 0;
		$used = $total - $free;
		$percentUsed = ($used / $total) * 100;

		// Используется только для дополнительных целей, например, подставим их
		return [
			'total' => round($total),
			'free' => round($free),
			'percent' => $percentUsed
		];
	}
	
	// Новая функция для получения информации о монтировании с учетом bind
	function getMountInfo($path) {
		// Используем findmnt для получения информации
		$output = shell_exec("sudo findmnt -rn -oTARGET,SOURCE,OPTIONS --target '$path'");
		// Пример строки: /media/user/usb /dev/sdb1 rw,relatime,bind  (поиск по TARGET)

		// Также можно использовать findmnt в формате JSON для более надежного парсинга
		$jsonOutput = shell_exec("sudo findmnt -J -o TARGET,SOURCE,OPTIONS '$path'");
		$data = json_decode($jsonOutput, true);

		if (!$data || empty($data['filesystems'])) return null;

		// Ищем подходящее монтирование
		foreach ($data['filesystems'] as $fs) {
			// Проверка на опцию bind
			$options = explode(',', $fs['options'] ?? '');
			if (in_array('bind', $options)) {
				// Возвращаем исходник для bind-монтирования
				return [
					'mount_point' => $fs['target'],
					'source' => $fs['source'],
					'isBind' => true
				];
			}
		}

		// Если не найдено bind, возвращаем стандартный
		return [
			'mount_point' => $data['filesystems'][0]['target'],
			'source' => $data['filesystems'][0]['source'],
			'options' => $data['filesystems'][0]['options'],
			'isBind' => false
		];
	}
	
	// Замена scandir() на ls с sudo
	function getFilesWithSudo($directory) {
		// Выполняем команду ls, исключая . и ..
		$command = "sudo ls -A \"$directory\"";
		$output = shell_exec($command);
		if ($output === null) {
			return [];
		}
		// Разбиваем результат по строкам
		$files = preg_split('/\s+/', trim($output));
		return $files;
	}
	
	function checkIsDirWithSudo($path) {
		$command = "sudo test -d '$path' && echo 'dir' || echo 'not_dir'";
		$result = shell_exec($command);
		return trim($result) === 'dir';
	}
	
	function parseSizeToBytes($sizeStr) {
		// Убираем пробелы
		$sizeStr = trim($sizeStr);
		
		// Регулярное выражение для разделения числа и суффикса
		if (!preg_match('/([\d.]+)\s*([KMGTP]?)/i', $sizeStr, $matches)) {
			return false; // Не удалось распарсить
		}
		
		$number = floatval(round($matches[1]));
		$unit = strtoupper($matches[2]);
		
		// Таблица множителей
		$multipliers = array(
			''  => 1,
			'K' => 1024,
			'M' => 1024 * 1024,
			'G' => 1024 * 1024 * 1024,
			'T' => 1024 * 1024 * 1024 * 1024,
			'P' => 1024 * 1024 * 1024 * 1024 * 1024,
		);
		
		return intval($number * $multipliers[$unit]);
	}

    foreach($pieces as $tmp){
		if($tmp != "/dev" || $tmp != ""){
		   $exp = explode(" ", $tmp);
		   $exp1 = array_diff($exp, array(''));		  		   		   		             
		   if(count($exp) > 1){
		       $array = array(); 
			   foreach($exp1 as $deb){
				   $array[count($array)] = $deb;
			   }
			   $allMountPoint[] = $array[5];
			   $allStorageSize = $allStorageSize + parseSizeToBytes($array[1]);
			   $allFreeStorageSize = $allFreeStorageSize + parseSizeToBytes($array[3]);
			   echo "<tr>";
			   echo "<td>";
			   echo "<p>" . $array[5] . " (" . $array[0]. ") (<span class='hddtemp' id='temp_".$array[0]."'></span>)" . "</p>".(($array[5] != "/") ? "<button onclick=\"umounting('".$array[5]."')\">Отмонтировать</button>" : "");
			   echo "</td>";
			   echo "<td>";
			   echo "<div class=\"storage-text".((intval($array[4]) > 90) ? " smallFree" : ((intval($array[4]) > 80) ? " mediumFree" : ""))."\">" . $array[3] . "/" . $array[1] . " (" . $array[4] . ")" . "</div>";
			   echo "</td>";
			   echo "</tr>";
		   }		  
		}			         		
	}

	if (is_dir($mediaDir)) {
		$dirs = getFilesWithSudo($mediaDir);
		foreach ($dirs as $dir) {
			if ($dir === '.' || $dir === '..') continue;

			$fullPath = $mediaDir . '/' . $dir;

			if (checkIsDirWithSudo($fullPath)) {

				// Проверка, есть ли эта директория в allMountPoint
				if (in_array($fullPath, $allMountPoint)) {
					continue;
				}

				// Проверка, есть ли файлы
				$files = array_diff(getFilesWithSudo($fullPath), ['.', '..']);
				if (count($files) === 0) continue;

				// Получение информации о монтировании
				$mountInfo = getMountInfo($fullPath);
				if (!$mountInfo) continue;

				$sourcePath = $mountInfo['source'];
				$isBind = $mountInfo['isBind'];

				// Для bind-монтирования использовать исходный источник
				$displayPath = $isBind ? $sourcePath : $mountInfo['mount_point'];

				// Получаем место filesystem
				$usage = getDiskUsage($displayPath);

				// Выводим строку таблицы
				echo "<tr>";
				echo "<td><p>$fullPath ($sourcePath)</p></td>";
				echo "<td>
					<div class=\"storage-text".((intval($array[4]) > 90) ? " smallFree" : ((intval($array[4]) > 80) ? " mediumFree" : ""))."\">
					".formatFileSize($usage['free'])."/".formatFileSize($usage['total'])." (".round($usage['percent'], 0)."%)
					</div>
					</td>";
				echo "</tr>";
			}
		}
	} else {
		echo "<tr><td colspan='5'>Директория /media не найдена</td></tr>";
	}
	
	
					
	
	echo "<tr>";
	echo "<td>";
	echo "<p>WEB-Server (".__DIR__.")</p>";
	echo "</td>";
	echo "<td>";
	echo "<p>" . formatFileSize(disk_free_space(__DIR__)) . "/" . formatFileSize(disk_total_space(__DIR__)) . "</p>";
	echo "</td>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<td>";
	echo "<p>Текущий сервер (".exec("hostname").")</p>";
	echo "</td>";
	echo "<td>";
	echo "<p>" . formatFileSize($allFreeStorageSize, 2) . "/" . formatFileSize($allStorageSize, 2) . "</p>";
	echo "</td>";
	echo "</tr>";
?> 
  </table>
  </div>
  <div>
  <p style="text-align: center;font-size:20px;">Нагрузка на систему:</p>
	<div id="sysLoad"></div>
  </div>
  <div>
  <a href='console.php'>Открыть консоль</a>
  </div>
  <div>
  <!--<div>
      <p style="text-align: center;font-size:20px;">Bluetooth музыка:</p>
        <span style="color: orange"> Controller B8:27:EB:30:C4:A3 raspberrypi [default] </span><br>
        <span style="color: green"> Device 70:4D:7B:75:0A:E0 ASUS_X014D </span><br>
        <span style="color: green"> Device 44:C3:46:DD:BE:DD Honor 5C </span><br>
      </p>
      <p>MAC <input id="text" type="text"></input></p>
      <div id="scan"></div>
      <button onclick="Playmac(document.getElementById('text').value)">Воспроизведение</button>
      <button onclick="Pairmac(document.getElementById('text').value)">Сопряжение</button>
      <button onclick="Scanefir(document.getElementById('text').value)">Скан эфира</button><br>
      <button onclick="moduleoff()">Выключить модуль</button>
      <button onclick="moduleon()">Включить модуль</button>
  </div>-->
  <p style="text-align: center;font-size:20px;">Мониторинг температуры:</p>
  <button onclick="GetTemp()">Обновить</button>
  <p><pre id="temp">Temperature is system</pre></p>
  </div>
</body>
</html>
