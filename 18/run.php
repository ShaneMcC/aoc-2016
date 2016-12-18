#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();
	$rows[] = str_split($input);

	function drawRows($rows) {
		echo '┍', str_repeat('━', count($rows[0])), '┑', "\n";
		foreach ($rows as $row) { echo '│', implode('', $row), '│', "\n"; }
		echo '┕', str_repeat('━', count($rows[0])), '┙', "\n";
	}

	function addRow($rows) {
		$lastRow = $rows[count($rows) - 1];
		$rows[] = nextRow($lastRow);
		return $rows;
	}

	function nextRow($lastRow) {
		$newRow = [];
		for ($i = 0; $i < count($lastRow); $i++) {
			$left = isset($lastRow[$i - 1]) ? $lastRow[$i - 1] : '.';
			$center = $lastRow[$i];
			$right = isset($lastRow[$i + 1]) ? $lastRow[$i + 1] : '.';

			$trap = ($left == '^' && $center == '^' && $right == '.') ||
			        ($left == '.' && $center == '^' && $right == '^') ||
			        ($left == '^' && $center == '.' && $right == '.') ||
			        ($left == '.' && $center == '.' && $right == '^');

			$newRow[$i] = $trap ? '^' : '.';
		}

		return $newRow;
	}

	for ($i = 1; $i < (isTest() ? 10 : 40); $i++) { $rows = addRow($rows); }
	if (isDebug()) { drawRows($rows); }

	$part1 = 0;
	foreach ($rows as $row) { $part1 += substr_count(implode('', $row), '.'); }
	echo 'Part 1: ', $part1, "\n";

	if (!isTest()) {
		$row = str_split($input);
		$part2 = substr_count(implode('', $row), '.');

		for ($i = 1; $i < 400000; $i++) {
			$row = nextRow($row);
			$part2 += substr_count(implode('', $row), '.');
			debugOut($i, ': ', $part2, "\n");
		}

		echo 'Part 2: ', $part2, "\n";
	}
