#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$counts = [];
	$part1 = $part2 = '';
	foreach ($input as $line) {
		for ($i = 0; $i < strlen($line); $i++) {
			$letter = $line{$i};
			if (!isset($counts[$i][$letter])) { $counts[$i][$letter] = 1; } else { $counts[$i][$letter]++; }
		}
	}

	foreach ($counts as $i => $letters) {
		arsort($letters);
		$keys = array_keys($letters);
		$part1 .= array_shift($keys);
		$part2 .= array_pop($keys);
	}

	echo 'Part 1: ', $part1, "\n";
	echo 'Part 2: ', $part2, "\n";
