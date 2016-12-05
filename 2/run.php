#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	function getCode($keypad, $start, $input) {
		$code = '';
		$dirs = ['U' => [-1, 0], 'L' => [0, -1], 'D' => [1, 0], 'R' => [0, 1]];
		$pos = $start;

		foreach ($input as $line) {
			foreach (str_split($line) as $i) {
				if (isset($dirs[$i])) {
					// Move according to direction.
					$newpos[0] = $pos[0] + $dirs[$i][0];
					$newpos[1] = $pos[1] + $dirs[$i][1];
					// Check if button is valid.
					if (isset($keypad[$newpos[0]][$newpos[1]]) && $keypad[$newpos[0]][$newpos[1]] !== FALSE) {
						$pos = $newpos;
					}
				}
			}

			$code .= $keypad[$pos[0]][$pos[1]];
		}

		return $code;
	}

	$keypad1 = [[1, 2, 3],
	            [4, 5, 6],
	            [7, 8, 9],
	           ];
	$start1 = [1, 1];

	$keypad2 = [[FALSE, FALSE, 1, FALSE, FALSE],
	            [FALSE, 2, 3, 4, FALSE],
	            [5, 6, 7, 8, 9],
	            [FALSE, 'A', 'B', 'C', FALSE],
	            [FALSE, FALSE, 'D', FALSE, FALSE],
	           ];
	$start2 = [3, 0];

	echo 'Part 1: ', getCode($keypad1, $start1, $input), "\n";
	echo 'Part 2: ', getCode($keypad2, $start2, $input), "\n";

