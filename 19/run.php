#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

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
	// From there until half way to the next number in the sequence, the elves
	// sequentially get the present from 1..2..3..4 etc. After the mid point,
	// every other elf gets it...
	//
	// I'm sure there is a formula here somewhere, I just don't know it yet...
	function doPart2($input) {
		$i = pow(3, floor(log($input - 1, 3))) + 1;
		$nextI = ($i * 3) - 2;

		$mid = ($i + $nextI) / 2;

		if ($input < $mid) {
			return ($input - $i) + 1;
		} else {
			return (2 * $input) - $mid - $i + 2;
		}
	}

	$part2 = doPart2($input);
	echo 'Part 2: ', $part2, "\n";
