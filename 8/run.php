#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$screenChars = [' ', '█'];

	if (isTest()) {
		$screen = array_fill(0, 3, array_fill(0, 7, $screenChars[false]));
	} else {
		$screen = array_fill(0, 6, array_fill(0, 50, $screenChars[false]));
	}

	function drawScreen($screen, $redraw = false) {
		// Redraw over previous screen by moving the cursor up.
		if ($redraw) { echo "\033[" . (count($screen) + 2) . "A"; }

		echo '┍', str_repeat('━', count($screen[0])), '┑', "\n";
		foreach ($screen as $row) { echo '│', implode('', $row), '│', "\n"; }
		echo '┕', str_repeat('━', count($screen[0])), '┙', "\n";
	}

	function rotateArray($array, $count) {
		for ($i = 0; $i < $count; $i++) {
			array_unshift($array, array_pop($array));
		}

		return $array;
	}

	function getScreenCharacters($screen) {
		$characters = array();

		foreach ($screen as $row) {
			for ($i = 0; $i < count($screen[0]); $i += 5) {
				$c = $i / 5;
				$characters[$c][] = array_slice($row, $i, 5);
			}
		}

		return $characters;
	}

	// From: https://www.reddit.com/r/adventofcode/comments/5h52ro/2016_day_8_solutions/daxv8cr/
	// Added missing characters as 0xFF for now to make them stand out.
	// Added N and V from https://www.reddit.com/r/adventofcode/comments/5h9sfd/2016_day_8_tampering_detected/
	$encodedCharacters = [0x19297A52 => 'A', 0x392E4A5C => 'B', 0x1928424C => 'C',
	                      0x39294A5C => 'D', 0x3D0E421E => 'E', 0x3D0E4210 => 'F',
	                      0x19285A4E => 'G', 0x252F4A52 => 'H', 0x1C42108E => 'I',
	                      0x0C210A4C => 'J', 0x254C5292 => 'K', 0x2108421E => 'L',
	                      0xFF       => 'M', 0x252D5A52 => 'N', 0x19294A4C => 'O',
	                      0x39297210 => 'P', 0xFF       => 'Q', 0x39297292 => 'R',
	                      0x1D08305C => 'S', 0x1C421084 => 'T', 0x25294A4C => 'U',
	                      0x2318A944 => 'V', 0xFF       => 'W', 0xFF       => 'X',
	                      0x23151084 => 'Y', 0x3C22221E => 'Z'];

	function decodeCharacter($character) {
		global $encodedCharacters, $screenChars;
		$char = (int)bindec(str_replace($screenChars, [0, 1], implode('', array_map('implode', $character))));

		return isset($encodedCharacters[$char]) ? $encodedCharacters[$char] : '?';
	}


	if (isDebug()) { drawScreen($screen, false); }

	foreach ($input as $details) {
		preg_match('#^(rect|rotate) (?:(row|column) (?:x|y)=([0-9]+) by ([0-9]+)|([0-9]+)x([0-9]+))#', trim($details), $m);
		$instr = $m[1];

		if ($instr == "rect") {
			list($all, $instr, $_, $_, $_, $rX, $rY) = $m;
			foreach (yieldXY(0, 0, $rX-1, $rY-1) as $col => $row) { $screen[$row][$col] = $screenChars[true]; }

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

		if (isDebug()) { drawScreen($screen, true); usleep(25000); }
	}

	if (isDebug()) { echo "\n"; }

	$part1 = 0;
	foreach ($screen as $row) { $part1 += substr_count(implode('', $row), $screenChars[true]); }

	echo 'Part 1: ', $part1, "\n";

	$part2 = '';
	if (!isTest()) {
		$characters = getScreenCharacters($screen);
		foreach ($characters as $c) { $part2 .= decodeCharacter($c); }
		echo 'Part 2: ', $part2, "\n";
	}

	if (!isDebug() && (isTest() || empty($part2) || strpos($part2, '?') !== FALSE)) {
		drawScreen($screen, false);
	}
