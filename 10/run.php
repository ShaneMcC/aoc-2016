#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$inputs = [];
	$bots = [];
	$outputs = [];

	$valueRegex = '#value ([0-9]+) goes to bot ([0-9]+)#SADi';
	$botRegex = '#bot ([0-9]+) gives low to (output|bot) ([0-9]+) and high to (output|bot) ([0-9]+)#SADi';

	// Parse out the instructions.
	foreach ($input as $instruction) {
		if (preg_match($valueRegex, $instruction, $m)) {
			list($all, $value, $bot) = $m;
			$inputs[$value] = $bot;

		} elseif (preg_match($botRegex, $instruction, $m)) {
			list($all, $bot, $lowDestType, $lowDest, $highDestType, $highDest) = $m;

			$bots[$bot] = ['lowDestType' => $lowDestType, 'lowDest' => $lowDest, 'highDestType' => $highDestType, 'highDest' => $highDest, 'values' => []];
		}
	}

	// Function to give values to destinations
	function giveValue($type, $id, $value) {
		global $bots, $outputs;
		if ($type == 'output') {
			if (!isset($outputs[$id])) { $outputs[$id] = []; }
			$outputs[$id][] = $value;
		} else if ($type == 'bot') {
			$bots[$id]['values'][] = $value;
		}
		debugOut("\t", sprintf('%6s %3d was given [%2d]', $type, $id, $value), "\n");
	}

	// Hand out initial values.
	foreach ($inputs as $val => $bot) { giveValue('bot', $bot, $val); }

	$part1 = -1;
	$part1Test = isTest() ? [2, 5] : [17, 61];

	// Find any bots with 2 values and action them.
	// Keep going until no more bots have 2 values.
	while (true) {
		$twoValues = array_filter($bots, function($bot) { return count($bot['values']) == 2; });
		if (count($twoValues) == 0) { break; }

		foreach ($twoValues as $botID => $bot) {
			sort($bot['values']);
			list($low, $high) = $bot['values'];

			if ($low == $part1Test[0] && $high == $part1Test[1]) { $part1 = $botID; }

			debugOut(sprintf('Bot %3d gives low [%2d] to %6s %3d, high [%2d] to %6s %3d', $botID, $low, $bot['lowDestType'], $bot['lowDest'], $high, $bot['highDestType'], $bot['highDest']), "\n");
			$bots[$botID]['values'] = [];
			giveValue($bot['lowDestType'], $bot['lowDest'], $low);
			giveValue($bot['highDestType'], $bot['highDest'], $high);
		}
	}

	echo 'Part 1: ', $part1, "\n";
