#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	function isValidSector($name, $checksum) {
		$letters = [];
		foreach (str_split(str_replace('-', '', $name)) as $bit) {
			if (!isset($letters[$bit])) { $letters[$bit] = 0; }
			$letters[$bit]++;
		}

		uksort($letters, function ($a, $b) use ($letters) {
			if ($letters[$a] == $letters[$b]) {
				return strcmp($a, $b);
			}
			return $letters[$b] - $letters[$a];
		});

		$calcSum = implode('', array_slice(array_keys($letters), 0, 5));
		return [$calcSum == $checksum, $calcSum];
	}

	function decryptSectorName($name, $sector) {
		$letters = str_split('abcdefghijklmnopqrstuvqxyz');

		$name = str_split($name);
		for ($i = 0; $i < count($name); $i++) {
			if ($name[$i] == '-') {
				$name[$i] = ' ';
			} else {
				$name[$i] = $letters[(array_search($name[$i], $letters) + $sector) % count($letters)];
			}
		}

		return implode('', $name);
	}

	$part1 = $part2 = 0;
	foreach ($input as $details) {
		preg_match('#([a-z\-]+)-([0-9]+)\[([a-z]{5})\]#i', $details, $m);
		list($all, $name, $sector, $checksum) = $m;

		list($valid, $realsum) = isValidSector($name, $checksum);
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
