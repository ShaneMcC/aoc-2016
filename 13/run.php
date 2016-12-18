#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	$start = [1, 1];
	$end = isTest() ? [7, 4] : [31, 39];

	function isWall($x, $y) {
		global $input;

		$val = (($x * $x) + (3 * $x) + (2 * $x * $y) + ($y) + ($y * $y)) + $input;
		$even = substr_count(decbin($val), '1') % 2 == 0;

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

	function drawState($state) {
		$maxX = max($state['current'][0], $state['target'][0]);
		$maxY = max($state['current'][1], $state['target'][1]);
		foreach ($state['previous'] as $prev) {
			$maxX = max($prev[0], $maxX);
			$maxY = max($prev[1], $maxY);
		}

		$mazeChars = [' ', '█'];
		$maze = array_fill(0, $maxY + 2, array_fill(0, $maxX + 2, $mazeChars[false]));
		foreach (yieldXY(0, 0, count($maze[0]), count($maze)) as $x => $y) {
			$maze[$y][$x] = $mazeChars[isWall($x, $y)];
		}
		foreach ($state['previous'] as $prev) { $maze[$prev[1]][$prev[0]] = 'X'; }
		$maze[$state['target'][1]][$state['target'][0]] = 'T';
		$maze[$state['current'][1]][$state['current'][0]] = 'C';

		echo '┍', str_repeat('━', count($maze[0])), '┑', "\n";
		foreach ($maze as $row) { echo '│', implode('', $row), '│', "\n"; }
		echo '┕', str_repeat('━', count($maze[0])), '┙', "\n";
	}


	function run($beginState) {
		$visted = [$beginState['current']];
		$states = [$beginState];

		while (count($states) > 0) {
			$state = array_shift($states);

			if (isFinished($state)) {
				debugOut('Finished With: [', $state['steps'], '] {', implode(', ', $state['current']), '}', "\n");
				if (isDebug()) { drawState($state); }
				return $state;
			} else {
				debugOut('Testing: [', $state['steps'], '] {', implode(', ', $state['current']), '}', "\n");
				if (isDebug()) { drawState($state); }
			}

			$options = getOptions($state);
			debugOut("\t", 'Found Options: ', count($options), "\n");
			foreach ($options as $opt) {
				if (!in_array($opt['current'], $visted)) {
					$visted[] = $opt['current'];
					$states[] = $opt;

					debugOut("\t\t", 'New Option: ', implode(', ', $opt['current']), "\n");
				}
			}
		}

		die('Unable to continue, no answer found.' . "\n");
	}

	$part1State = $initialState;
	$part1 = run($part1State);

	echo 'Part 1: ', $part1['steps'], "\n";
