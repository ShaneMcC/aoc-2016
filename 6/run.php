#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$lines = [];
	$part1 = $part2 = '';
	$lines = array_map('str_split', $input);

	for ($i = 0; $i < count($lines[0]); $i++) {
		$keys = count_chars(implode('', array_column($lines, $i)), 1);
		arsort($keys);
		$keys = array_keys($keys);
		$part1 .= chr(array_shift($keys));
		$part2 .= chr(array_pop($keys));
	}

	echo 'Part 1: ', $part1, "\n";
	echo 'Part 2: ', $part2, "\n";
