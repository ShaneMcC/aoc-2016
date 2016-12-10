#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$inputs = [];
	$outputs = [];

	$part1 = -1;
	$part2 = -1;
	$part1Test = isTest() ? [2, 5] : [17, 61];

	$valueRegex = '#value ([0-9]+) goes to (bot [0-9]+)#SADi';
	$botRegex = '#(bot [0-9]+) gives low to ((?:output|bot) [0-9]+) and high to ((?:output|bot) [0-9]+)#SADi';

	// Parse out the instructions.
	foreach ($input as $instruction) {
		if (preg_match($valueRegex, $instruction, $m)) {
			list($all, $value, $target) = $m;
			$inputs[$value] = $target;

		} elseif (preg_match($botRegex, $instruction, $m)) {
			list($all, $output, $lowDest, $highDest) = $m;

			$outputs[$output] = ['lowDest' => $lowDest, 'highDest' => $highDest, 'values' => []];
		}
	}

	// Function to give values to destinations
	function giveValue($target, $value) {
		global $outputs;
		debugOut("\t", sprintf('%s was given [%2d]', $target, $value), "\n");
		if (!isset($outputs[$target])) { $outputs[$target] = ['values' => '']; }
		$outputs[$target]['values'][] = $value;
		processOutput($target);
	}

	// If a bot ends up with 2 values, process it.
	function processOutput($id) {
		global $outputs, $part1, $part1Test;
		$out = $outputs[$id];

		if (!isset($out['lowDest']) || !isset($out['highDest'])) { return; }
		if (count($out['values']) != 2) { return; }

		sort($out['values']);
		list($low, $high) = $out['values'];

		if ($low == $part1Test[0] && $high == $part1Test[1]) { $part1 = $id; }

		debugOut(sprintf('%s gives low [%2d] to %s, high [%2d] to %s', $id, $low, $out['lowDest'], $high, $out['highDest']), "\n");
		$outputs[$id]['values'] = [];
		giveValue($out['lowDest'], $low);
		giveValue($out['highDest'], $high);
	}

	// Hand out values, this will trigger all the bots to do things as needed.
	foreach ($inputs as $val => $out) { giveValue($out, $val); }

	$part2 = [];
	for ($i = 0; $i <= 2; $i++) { $part2[] = $outputs['output '.$i]['values'][0] ?: ''; }
	$part2 = array_product(array_filter($part2));

	echo 'Part 1: ', $part1, "\n";
	echo 'Part 2: ', $part2, "\n";

	if (isDebug()) {
		echo "\n";
		ksort($outputs, SORT_NATURAL);
		foreach ($outputs as $id => $data) { echo sprintf('%s: [%s]', $id, implode(', ', $data['values'])), "\n"; }
	}
