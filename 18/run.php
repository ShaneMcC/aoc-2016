#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();
	$lastRow = $input;

	function nextRow($lastRow) {
		$newRow = '';
		for ($i = 0; $i < strlen($lastRow); $i++) {
			$newRow .= (($i > 0 ? $lastRow{$i - 1} : '.') != ($i < strlen($lastRow) - 1 ? $lastRow{$i + 1} : '.')) ? '^' : '.';
		}
		return $newRow;
	}

	function countSafe($input, $rows) {
		$row = $input;
		$count = substr_count($row, '.');
		for ($i = 1; $i < $rows; $i++) {
			$row = nextRow($row);
			$count += substr_count($row, '.');
			debugOut("\r", $i, ' => ', $count, "\033[0K");
		}
		debugOut("\r\033[0K\r");
		return $count;
	}

	$part1 = countSafe($input, isTest() ? 10 : 40);
	echo 'Part 1: ', $part1, "\n";

	if (!isTest()) {
		$part2 = countSafe($input, 400000);
		echo 'Part 2: ', $part2, "\n";
	}
