#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	if (isset($__CLIOPTS['brute'])) { doBruteForce(); }

	// If an elf count is a power of 2, elf #1 gets the presents.
	// For every number above that, the next odd elf gets it.
	function doPart1($input) {
		return 1 + (2 * ($input - pow(2, floor(log($input, 2)))));
	}

	$part1 = doPart1($input);
	echo 'Part 1: ', $part1, "\n";

	// Elf 1 gets the present every time there is a number of elves
	// equal to the sequence of "(x*3) - 2"
	// 2 4 10 28 82 244 730 ...
	// (or, the powers of 3 (1 3 9 27 81) + 1)
	//
	// From there until (3^n * 2) the elves sequentially get the present from
	// 1..2..3..4 etc. After wards, every other elf from there onwards gets it.
	function doPart2($input) {
		$i = pow(3, floor(log($input, 3)));

		if ($input == $i) {
			return $i;
		} else if ($input <= ($i * 2)) {
			return ($input - $i);
		} else {
			return (2 * $input) - (3 * $i);
		}
	}

	$part2 = doPart2($input);
	echo 'Part 2: ', $part2, "\n";
