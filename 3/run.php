#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	function getSides($line) {
		preg_match('#^([0-9]+)\s+([0-9]+)\s+([0-9]+)$#', trim($line), $sides);
		array_shift($sides);
		return $sides;
	}

	function isPossible($sides) {
		sort($sides);
		return ($sides[0] + $sides[1] > $sides[2]);
	}

	$total = $possible = 0;
	foreach ($input as $details) {
		if (isPossible(getSides($details))) { $possible++; }
		$total++;
	}

	echo 'Part 1: ', $possible, ' triangles are possible out of ', $total, "\n";


	$total = $possible = 0;
	$lines = [];
	for ($i = 0; $i < count($input); $i += 3) {
		$lines[] = getSides($input[$i]);
		$lines[] = getSides($input[$i + 1]);
		$lines[] = getSides($input[$i + 2]);

		if (isPossible([$lines[$i][0], $lines[$i + 1][0], $lines[$i + 2][0]])) { $possible++; }
		if (isPossible([$lines[$i][1], $lines[$i + 1][1], $lines[$i + 2][1]])) { $possible++; }
		if (isPossible([$lines[$i][2], $lines[$i + 1][2], $lines[$i + 2][2]])) { $possible++; }
		$total += 3;
	}

	echo 'Part 2: ', $possible, ' triangles are possible out of ', $total, "\n";
