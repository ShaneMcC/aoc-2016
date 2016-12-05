#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$code = [];

	$keypad = [[1, 2, 3],
	           [4, 5, 6],
	           [7, 8, 9],
	          ];

	$dirs = ['U' => [-1, 0], 'L' => [0, -1], 'D' => [1, 0], 'R' => [0, 1]];
	$pos = [1, 1];

	foreach ($input as $line) {
		foreach (str_split($line) as $i) {
			if (isset($dirs[$i])) {
				// Move according to direction.
				$newpos[0] = $pos[0] + $dirs[$i][0];
				$newpos[1] = $pos[1] + $dirs[$i][1];
				// Check if button is valid.
				if (isset($keypad[$newpos[0]][$newpos[1]])) {
					$pos = $newpos;
				}
			}
		}

		$code[] = $keypad[$pos[0]][$pos[1]];
	}

	echo 'Part 1: ', implode('', $code), "\n";

