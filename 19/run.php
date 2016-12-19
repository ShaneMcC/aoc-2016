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
	//
	// From there until half way to the next number in the sequence, the elves
	// sequentially get the present from 1..2..3..4 etc. After the mid point,
	// every other elf gets it...
	//
	// I'm sure there is a formula here somewhere, I just don't know it yet...
	function doPart2($input) {
		$i = 2;
		while (true) {
			$j = ($i * 3) - 2;
			if ($j <= $input) { $i = $j; } else { break; }
		}
		$nextI = ($i * 3) - 2;

		$mid = $i + (($nextI - $i) / 2);

		if ($input < $mid) {
			return ($input - $i) + 1;
		} else {
			$midNum = ($mid - $i) + 1;
			$loc = ($input - $mid) + 1;

			return (2 * $loc) + $midNum - 1;
		}
	}

	$part2 = doPart2($input);
	echo 'Part 2: ', $part2, "\n";
