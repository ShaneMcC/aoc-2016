#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	if (isTest()) {
		$screen = array_fill(0, 3, array_fill(0, 7, ' '));
	} else {
		$screen = array_fill(0, 6, array_fill(0, 50, ' '));
	}

	function drawScreen($screen) {
		echo '==========', "\n";
		foreach ($screen as $row) { echo "\t", implode('', $row), "\n"; }
		echo '==========', "\n";
	}

	function rotateArray($array, $count) {
		for ($i = 0; $i < $count; $i++) {
			array_unshift($array, array_pop($array));
		}

		return $array;
	}

	foreach ($input as $details) {
		preg_match('#^(rect|rotate) (?:(row|column) (?:x|y)=([0-9]+) by ([0-9]+)|([0-9]+)x([0-9]+))#', trim($details), $m);
		$instr = $m[1];

		if ($instr == "rect") {
			list($all, $instr, $_, $_, $_, $rX, $rY) = $m;
			foreach (yieldXY(0, 0, $rX-1, $rY-1) as $col => $row) { $screen[$row][$col] = '#'; }

		} else if ($instr == "rotate") {
			list($all, $instr, $type, $which, $by) = $m;

			if ($type == "row") {
				$screen[$which] = rotateArray($screen[$which], $by);
			} else if ($type == "column") {
				$col = rotateArray(array_column($screen, $which), $by);
				// Merge the column back into the array.
				for ($i = 0; $i < count($col); $i++) { $screen[$i][$which] = $col[$i]; }
			}
		}

		if (isDebug()) { drawScreen($screen); }
	}

	$part1 = 0;
	foreach ($screen as $row) { $part1 += substr_count(implode('', $row), '#'); }

	echo 'Part 1: ', $part1, "\n";
	echo 'Part 2: ', "\n";
	drawScreen($screen);

	// TODO: Calculate letter from screen...
