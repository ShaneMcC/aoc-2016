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
