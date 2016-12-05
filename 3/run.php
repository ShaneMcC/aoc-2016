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


	$total = $possible = 0;
	$lines = [];
	foreach ($input as $details) {
		preg_match('#^([0-9]+)\s+([0-9]+)\s+([0-9]+)$#', trim($details), $sides);
		array_shift($sides);
		$lines[] = $sides;
	}

	for ($i = 0; $i < count($lines); $i += 3) {
		$t1 = [$lines[$i][0], $lines[$i + 1][0], $lines[$i + 2][0]];
		$t2 = [$lines[$i][1], $lines[$i + 1][1], $lines[$i + 2][1]];
		$t3 = [$lines[$i][2], $lines[$i + 1][2], $lines[$i + 2][2]];
		sort($t1);
		sort($t2);
		sort($t3);

		if ($t1[0] + $t1[1] > $t1[2]) { $possible++; }
		if ($t2[0] + $t2[1] > $t2[2]) { $possible++; }
		if ($t3[0] + $t3[1] > $t3[2]) { $possible++; }

		$total += 3;
	}


	echo 'Part 2: ', $possible, ' triangles are possible out of ', $total, "\n";
