#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	if (isTest()) {
		$screen = array_fill(0, 3, array_fill(0, 7, '.'));
	} else {
		$screen = array_fill(0, 6, array_fill(0, 50, '.'));
	}

	function drawScreen($screen) {
		echo '==========', "\n";
		foreach ($screen as $row) { echo "\t", implode('', $row), "\n"; }
		echo '==========', "\n";
	}

	foreach ($input as $details) {
		preg_match('#^(.*?) (.*)#', trim($details), $m);
		list($all, $instr, $params) = $m;

		if ($instr == "rect") {
			preg_match('#([0-9]+)x([0-9]+)#', $params, $m);
			list($all, $wx, $wy) = $m;

			foreach (yieldXY(0, 0, $wx-1, $wy-1) as $x1 => $y1) {
				if (isset($screen[$y1][$x1])) {
					$screen[$y1][$x1] = '#';
				}
			}
		} else if ($instr == "rotate") {
			preg_match('#(row|column) (?:x|y)=([0-9]+) by ([0-9]+)#', $params, $m);
			list($all, $type, $which, $by) = $m;

			if ($type == "row") {
				for ($i = 0; $i < $by; $i++) {
					array_unshift($screen[$which], array_pop($screen[$which]));
				}
			} else if ($type == "column") {
				$col = array_column($screen, $which);
				for ($i = 0; $i < $by; $i++) {
					array_unshift($col, array_pop($col));
				}
				for ($i = 0; $i < count($col); $i++) {
					$screen[$i][$which] = $col[$i];
				}
			}
		}

		if (isDebug()) { drawScreen($screen); }
	}

	$part1 = 0;
	foreach ($screen as $row) { $part1 += substr_count(implode('', $row), '#'); }

	echo 'Part 1: ', $part1, "\n";
	echo 'Part 2: ', "\n";
	drawScreen($screen);
