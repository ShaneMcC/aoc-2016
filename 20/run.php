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

	function doPart1($blocked) {
		$min = 0;
		foreach ($blocked as $block) {
			debugOut('Blocked: ', $block['start'], ' => ', $block['end'], "\n");

			if ($min < $block['start']) {
				return $min;
			} else {
				$min = $block['end'] + 1;
			}
		}

		return -1;
	}

	$part1 = doPart1($blocked);
	echo 'Part 1: ', $part1, "\n";
