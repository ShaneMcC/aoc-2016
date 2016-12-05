#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$total = $possible = 0;
	foreach ($input as $details) {
		preg_match('#^([0-9]+)\s+([0-9]+)\s+([0-9]+)$#', trim($details), $sides);
		array_shift($sides);
		sort($sides);

		$total++;
		if ($sides[0] + $sides[1] > $sides[2]) { $possible++; }
	}

	echo 'Part 1: ', $possible, ' triangles are possible out of ', $total, "\n";


