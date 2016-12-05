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
	for ($i = 0; $i < count($input); $i += 3) {
		$line1 = getSides($input[$i]);
		$line2 = getSides($input[$i + 1]);
		$line3 = getSides($input[$i + 2]);

		if (isPossible([$line1[0], $line2[0], $line3[0]])) { $possible++; }
		if (isPossible([$line1[1], $line2[1], $line3[1]])) { $possible++; }
		if (isPossible([$line1[2], $line2[2], $line3[2]])) { $possible++; }
		$total += 3;
	}

	echo 'Part 2: ', $possible, ' triangles are possible out of ', $total, "\n";
