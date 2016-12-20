#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$blocked = array();
	foreach ($input as $details) {
		preg_match('#(.*)-(.*)#SADi', $details, $m);
		list($all, $start, $end) = $m;
		$blocked[] = array('start' => $start, 'end' => $end);
	}
	uasort($blocked, function($a, $b) { return ($a['start'] == $b['start']) ? $a['end'] - $b['end'] : $a['start'] - $b['start']; });

	function getAnswers($blocked) {
		$allowed = 0;
		$min = $thisStart = $nextEnd = -1;

		foreach ($blocked as $block) {
			debugOut('Blocked: ', $block['start'], ' => ', $block['end'], "\n");

			if ($block['start'] > $nextEnd + 1) {
				if ($min == -1) { $min = ($nextEnd + 1); }
				$allowed += $block['start'] - ($nextEnd + 1);
			}

			$thisStart = min($thisStart, $block['start']);
			$nextEnd = max($nextEnd, $block['end']);
		}

		$max = isTest() ? 9 : 4294967295;
		if ($nextEnd <= $max) { $allowed += $max - $nextEnd; }

		return [$min, $allowed];
	}
	list($part1, $part2) = getAnswers($blocked);

	echo 'Part 1: ', $part1, "\n";
	echo 'Part 2: ', $part2, "\n";
