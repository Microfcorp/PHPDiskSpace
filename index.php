<?php include_once("/var/www/html/site/secur.php"); ?>
<!Doctype html>
<html>
<head>
<title>10 корпус. Система</title>

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
	postAjax('console.php?command=sensors&cd=system', "", function(d){document.getElementById("temp").innerHTML = d.replaceAll("\\n", "<br />");})
	
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
	postAjax('mounting.php?type=umount&mount=' + data, "", function(d){alert(d);})
}

function mountall(data){
	postAjax('mounting.php?type=mountall&mount=' + data, "", function(d){alert(d);})
}
window.setTimeout(GetTemp(),1);
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
	$pizza  = shell_exec("sudo -u root bash devparse.sh 2>&1");
	$pieces = explode("\n", $pizza);
  
	function getTemp($disk){
		$temp = shell_exec("sudo -u root hddtemp $disk 2>&1");
		return trim(explode(':', $temp)[2]);
	}
  
	function formatFileSize($size) {
		$a = array("B", "KB", "MB", "GB", "TB", "PB");
		$pos = 0;
		while ($size >= 1024) {
			$size /= 1024;
			$pos++;
		}
		return round($size,2)." ".$a[$pos];
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
			   echo "<tr>";
			   echo "<td>";
			   echo "<p>" . $array[5] . " (" . $array[0]. ") (" . getTemp($array[0]) . ")" . "</p><button OnClick=\"umounting('".$array[5]."')\">Отмонтировать</button>";
			   echo "</td>";
			   echo "<td>";
			   echo "<p>" . $array[3] . "/" . $array[1] . " (" . $array[4] . ")" . "</p>";
			   echo "</td>";
			   echo "</tr>";
		   }		  
		}			         		
	}		
	echo "<tr>";
	echo "<td>";
	echo "<p>System (/mnt)</p>";
	echo "</td>";
	echo "<td>";
	echo "<p>" . formatFileSize(disk_free_space("/mnt")) . "/" . formatFileSize(disk_total_space("/mnt")) . "</p>";
	echo "</td>";
	echo "</tr>";
?> 
  </table>
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
  <p><span id="temp">Temperature is system</span></p>
  </div>
</body>
</html>
