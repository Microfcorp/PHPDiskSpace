<?php include_once("/var/www/html/site/secur.php"); ?>
<?php
	// Получение средней загрузки CPU за 1 минуту
	$loadavg = sys_getloadavg();
	$cores = floatval(shell_exec('sudo nproc'));
	$cpuLoadPercent = (($loadavg[0]) * 100) / $cores ; // средняя за 1 минуту
	
	function formatFileSize($size, $rd = 0) {
		$a = array("B", "KB", "MB", "GB", "TB", "PB");
		$pos = 0;
		while ($size >= 1024) {
			$size /= 1024;
			$pos++;
		}
		return round($size,$rd)." ".$a[$pos];
	}
	
	// Получение загрузки по ядрам (использование mpstat)
	function getCpuCoreUsage() {
		$jsonOutput = shell_exec("sudo mpstat -P ALL -o JSON 1 1");
		$data = json_decode($jsonOutput, true);
		if (!$data || !isset($data['sysstat']['hosts'][0]['statistics'][0]['cpu-load'])) {
			return [];
		}
		$stats = $data['sysstat']['hosts'][0]['statistics'][0]['cpu-load'];
		$coreUsages = [];

		foreach ($stats as $stat) {
			if (isset($stat['cpu'])) {
				$cpu = $stat['cpu'];
				if ($cpu == 'all') continue; // пропускаем общую статистику, если нужно
				$usage = 100 - floatval($stat['idle']); // использование
				$coreUsages[$cpu] = $usage;
			}
		}
		return $coreUsages;
	}

	// Получение памяти в гигабайтах и процентах
	function getMemoryUsage() {
		$totalMemStr = shell_exec("sudo free | grep Mem | awk '{print $2}'");
		$usedMemStr = shell_exec("sudo free | grep Mem | awk '{print $3}'");
		$totalMem = floatval($totalMemStr);
		$usedMem = floatval($usedMemStr);
		$percentUsed = ($usedMem / $totalMem) * 100;
		return [
			'total_gb' => $totalMem,
			'used_gb' => $usedMem,
			'percent' => $percentUsed
		];
	}

	// Получение информации о дисках
	function getDiskIO() {
		// Выполнение команды с JSON
		$json_output = shell_exec("sudo iostat -d -x 1 1 -o JSON");
		$data = json_decode($json_output, true);
		if (!$data || !isset($data['sysstat']['hosts'][0]['statistics'][0]['disk'])) {
			return [];
		}
		// В завимости от структуры, получаем данные про диски
		$disks = [];
		$stats = $data['sysstat']['hosts'][0]['statistics'][0]['disk'];
		foreach ($stats as $stat) {
			if (isset($stat['disk_device']) && strpos($stat['disk_device'], "sd") !== false) {
				$disks[] = [
					'device' => $stat['disk_device'],
					'kB_read/sec' => $stat['rkB/s']*1000,
					'kB_wrtn/sec' => $stat['wkB/s']*1000,
					'utilization_percent' => isset($stat['util']) ? $stat['util'] : null,
				];
			}
		}
		return $disks;
	}

	// Вывод результатов
	echo "<h5>Нагрузка CPU по ядрам</h5>";
	$coreUsages = getCpuCoreUsage();
	foreach ($coreUsages as $core => $usage) {
		echo "Ядро $core: " . round($usage, 2) . "%<br>";
	}
	echo "Средняя (за 1 минуту): " . round($cpuLoadPercent, 2) . "%<br>";	

	echo "<h5>Использование оперативной памяти</h5>";
	$memUsage = getMemoryUsage();
	echo "Всего: " . formatFileSize($memUsage['total_gb']*1024, 2) . "<br>";
	echo "Занято: " . formatFileSize($memUsage['used_gb']*1024, 2) . "<br>";
	echo "Процент использования: " . round($memUsage['percent'], 2) . "%<br>";

	echo "<h5>Нагрузка на дисковую подсистему</h5>";
	$disks = getDiskIO();
	echo "<table border=\"1\" width=\"35%\" cellpadding=\"3\">";
	echo "<tr><th>Устройство</th><th>Чтение</th><th>Запись</th><th>Загруженность</th></tr>";
	
	foreach ($disks as $disk) {
		echo "<tr>";
		echo "<td>" . $disk['device'] . "</td>";
		echo "<td>" . formatFileSize($disk['kB_read/sec']) . "/s</td>";
		echo "<td>" . formatFileSize($disk['kB_wrtn/sec']) . "/s</td>";
		echo "<td>" . round($disk['utilization_percent'], 2) . "%</td>";
		echo "</tr>";
	}
	echo "</table><hr>";
?>