#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$counts = [];
	$part1 = '';

	foreach ($input as $line) {
		$bits = str_split(trim($line));
		for ($i = 0; $i < count($bits); $i++) {
			$letter = $bits[$i];
			if (!isset($counts[$i])) { $counts[$i] = []; }
			if (!isset($counts[$i][$letter])) { $counts[$i][$letter] = 1; } else { $counts[$i][$letter]++; }
		}
	}

	foreach ($counts as $i => $letters) {
		uksort($letters, function ($a, $b) use ($letters) { return ($letters[$a] == $letters[$b]) ? strcmp($a, $b) : $letters[$b] - $letters[$a]; });
		$keys = array_keys($letters);
		$part1 .= array_shift($keys);
	}

	echo 'Part 1: ', $part1, "\n";
