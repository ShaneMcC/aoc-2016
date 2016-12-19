#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	$start = [1, 1];
	$end = isTest() ? [7, 4] : [31, 39];

	function isWall($x, $y, $knownOnly = false) {
		global $input, $__KNOWNWALLS;

		if ($knownOnly) { return isset($__KNOWNWALLS[$x.','.$y]) ? $__KNOWNWALLS[$x.','.$y] : FALSE; }

		$val = (($x * $x) + (3 * $x) + (2 * $x * $y) + ($y) + ($y * $y)) + $input;
		$even = substr_count(decbin($val), '1') % 2 == 0;

		$__KNOWNWALLS[$x.','.$y] = !$even;

		return !$even;
	}

	$initialState = array('current' => $start, 'target' => $end, 'previous' => [], 'steps' => 0);

	function isFinished($state) {
		return ($state['current'] == $state['target']);
	}

	function getOptions($state) {
		$curX = $state['current'][0];
		$curY = $state['current'][1];

		$options = [];
		foreach ([$curX - 1, $curX, $curX + 1] as $x) {
			foreach ([$curY - 1, $curY, $curY + 1] as $y) {
				if ($x < 0 || $y < 0) { continue; } // Ignore Negative
				if ($x != $curX && $y != $curY) { continue; } // Ignore Corners
				if ($x == $curX && $y == $curY) { continue; } // Ignore Current

				$new = [$x, $y];
				if (!isWall($x, $y) && !in_array($new, $state['previous'])) {
					$newState = $state;
					$newState['previous'][] = $newState['current'];
					$newState['current'] = $new;
					$newState['steps']++;
					$options[] = $newState;
				}
			}
		}

		return $options;
	}

	function solveMaze($beginState, $maxSteps = -1) {
		global $__CLIOPTS, $visted;
		$visted = [$beginState['current']];
		$states = [$beginState];

		if (isset($__CLIOPTS['drawSearch'])) { drawState($beginState, false); }

		$finalState = FALSE;

		while (count($states) > 0) {
			$state = array_shift($states);

			if ($maxSteps == -1 && isFinished($state)) {
				debugOut('Finished With: [', $state['steps'], '] {', implode(', ', $state['current']), '}', "\n");
				if (isDebug() || isset($__CLIOPTS['drawSearch'])) { drawState($state); }
				$finalState = $state;
				break;
			} else {
				debugOut('Testing: [', $state['steps'], '] {', implode(', ', $state['current']), '}', "\n");
				if (isDebug() || isset($__CLIOPTS['drawSearch'])) { drawState($state); }
			}

			$options = getOptions($state);
			debugOut("\t", 'Found Options: ', count($options), "\n");
			foreach ($options as $opt) {
				if (!in_array($opt['current'], $visted) && ($maxSteps <= 0 || $opt['steps'] <= $maxSteps)) {
					$visted[] = $opt['current'];
					$states[] = $opt;

					debugOut("\t\t", 'New Option: ', implode(', ', $opt['current']), "\n");
				}
			}
		}

		return [$finalState, $visted];
	}

	if (!isset($__CLIOPTS['2'])) {
		list($part1, $_) = solveMaze($initialState);
		echo 'Part 1: ', $part1['steps'], "\n";
	}


	if (!isset($__CLIOPTS['1'])) {
		$limit = isset($__CLIOPTS['limit']) ? $__CLIOPTS['limit'] : 50;
		list($_, $part2) = solveMaze($initialState, $limit);
		echo 'Part 2: ', count($part2), "\n";
	}
