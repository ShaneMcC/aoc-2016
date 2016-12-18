#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$discs = array();
	foreach ($input as $details) {
		preg_match('#Disc \#([0-9]+) has ([0-9]+) positions; at time=0, it is at position ([0-9]+).#SADi', $details, $m);
		list($all, $disc, $count, $start) = $m;
		$discs[$disc]  = ['count' => $count, 'start' => $start];
	}

	function canGetCapsule($discs, $startTime) {
		foreach ($discs as $number => $disc) {
			if (($disc['start'] + $startTime + $number) % $disc['count'] != 0) {
				return FALSE;
			}
		}

		return TRUE;
	}

	$part1 = 0;
	while (!canGetCapsule($discs, $part1)) { $part1++; }

	echo 'Part 1: ', $part1, "\n";
