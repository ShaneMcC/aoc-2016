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
		$min = $allowed = $thisStart = $nextEnd = 0;

		foreach ($blocked as $block) {
			debugOut('Blocked: ', $block['start'], ' => ', $block['end'], "\n");

			if ($block['start'] > $nextEnd) {
				if ($min == 0) { $min = $nextEnd; }
				$allowed += $block['start'] - $nextEnd;
			}

			$thisStart = min($thisStart, $block['start']);
			$nextEnd = max($nextEnd, $block['end'] + 1);
		}

		return [$min, $allowed];
	}
	list($part1, $part2) = getAnswers($blocked);

	echo 'Part 1: ', $part1, "\n";
	echo 'Part 2: ', $part2, "\n";
