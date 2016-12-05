#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	function getChecksum($name) {
		$letters = [];
		foreach (str_split(str_replace('-', '', $name)) as $bit) {
			if (!isset($letters[$bit])) { $letters[$bit] = 1; } else { $letters[$bit]++; }
		}
		uksort($letters, function ($a, $b) use ($letters) { return ($letters[$a] == $letters[$b]) ? strcmp($a, $b) : $letters[$b] - $letters[$a]; });
		return implode('', array_slice(array_keys($letters), 0, 5));
	}

	function decryptSectorName($name, $sector) {
		$name = str_split($name);
		for ($i = 0; $i < count($name); $i++) {
			$name[$i] = ($name[$i] == '-') ? ' ' : chr(((ord($name[$i]) + $sector - 97) % 26) + 97);
		}
		return implode('', $name);
	}

	$part1 = $part2 = 0;
	foreach ($input as $details) {
		preg_match('#([a-z\-]+)-([0-9]+)\[([a-z]{5})\]#i', $details, $m);
		list($all, $name, $sector, $checksum) = $m;

		$realsum = getChecksum($name);
		$valid = $realsum == $checksum;
		$decrypted = decryptSectorName($name, $sector);

		debugOut(sprintf('Sector %3s: %60s [%s == %s] => %-5s {%s}', $sector, $name, $checksum, $realsum, ($valid ? 'VALID' : ' BAD'), $decrypted), "\n");

		if ($valid) {
			$part1 += $sector;
			if ($decrypted == 'northpole object storage') { $part2 = $sector; }
		}
	}

	debugOut("\n");
	echo 'Part 1: ', $part1, "\n";
	echo 'Part 2: ', $part2, "\n";
