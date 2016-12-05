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
		return $calcSum == $checksum;
	}

	$part1 = 0;
	foreach ($input as $details) {
		preg_match('#([a-z\-]+)-([0-9]+)\[([a-z]{5})\]#i', $details, $m);
		list($all, $name, $sector, $checksum) = $m;

		$valid = isValidSector($name, $checksum);
		debugOut('Sector ', $sector, ': ', $name, ' [', $checksum, '] => ', ($valid ? 'VALID' : 'BAD'), "\n");

		if ($valid) { $part1 += $sector; }
	}

	echo 'Part 1: ', $part1, "\n";
