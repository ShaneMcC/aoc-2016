#!/usr/bin/php
<?php
	$__CLI['short'] = ['1', '2'];
	$__CLI['long'] = ['drawSearch', 'sleep:', 'limit:', 'width:', 'height:', 'fog'];
	$__CLI['extrahelp'] = [];
	$__CLI['extrahelp'][] = '  -1                       Only solve part 1';
	$__CLI['extrahelp'][] = '  -2                       Only solve part 2';
	$__CLI['extrahelp'][] = '      --limit <#>          Set part 2 limit to <#> rather than 50.';
	$__CLI['extrahelp'][] = '      --drawSearch         Draw the entire search in same location.';
	$__CLI['extrahelp'][] = '      --sleep <#>          Sleep time between output when using drawSearch';
	$__CLI['extrahelp'][] = '      --width <#>          Maze output width when using drawSearch';
	$__CLI['extrahelp'][] = '      --height <#>         Maze output height when using drawSearch';
	$__CLI['extrahelp'][] = '      --fog                "Fog of war" for walls in drawSearch - only show walls if we\'ve hit them.';

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

	function setMazeChar(&$maze, $x, $y, $char) {
		if (isset($maze[$y][$x])) { $maze[$y][$x] = $char; }
	}

	function drawState($state, $redraw = true) {
		global $__CLIOPTS, $visted;

		if (isset($__CLIOPTS['drawSearch'])) {
			$maxX = isset($__CLIOPTS['width']) ? $__CLIOPTS['width'] : (isTest() ? 10 : 50);
			$maxY = isset($__CLIOPTS['height']) ? $__CLIOPTS['height'] : (isTest() ? 10 : 50);

			// Redraw over previous screen by moving the cursor up.
			if ($redraw) { echo "\033[" . ($maxY + 3) . "A"; }

			$mazeChars = [' ', "\033[0;37m" . '█' . "\033[0m"];

			$wallFog = isset($__CLIOPTS['fog']);
		} else {
			$mazeChars = [' ', '█'];

			$maxX = max($state['current'][0], $state['target'][0]);
			$maxY = max($state['current'][1], $state['target'][1]);
			foreach ($state['previous'] as $prev) {
				$maxX = max($prev[0], $maxX);
				$maxY = max($prev[1], $maxY);
			}
			$maxX += 2;
			$maxY += 2;

			$wallFog = false;
		}

		$maze = array_fill(0, $maxY, array_fill(0, $maxX, $mazeChars[false]));
		foreach (yieldXY(0, 0, count($maze[0]), count($maze)) as $x => $y) {
			$maze[$y][$x] = $mazeChars[isWall($x, $y, $wallFog)];
		}

		if (isset($__CLIOPTS['drawSearch'])) {
			foreach ($visted as $v) { setMazeChar($maze, $v[0], $v[1], "\033[0;34m" . '█' . "\033[0m"); }
			foreach ($state['previous'] as $prev) { setMazeChar($maze, $prev[0], $prev[1], "\033[0;36m" . '█' . "\033[0m"); }
			setMazeChar($maze, $state['target'][0], $state['target'][1], "\033[1;31m" . '█' . "\033[0m");
			setMazeChar($maze, $state['current'][0], $state['current'][1], "\033[0;32m" . '█' . "\033[0m");
		} else {
			foreach ($state['previous'] as $prev) { $maze[$prev[1]][$prev[0]] = 'X'; }
			$maze[$state['target'][1]][$state['target'][0]] = 'T';
			$maze[$state['current'][1]][$state['current'][0]] = 'C';
		}

		echo '┍', str_repeat('━', count($maze[0])), '┑', "\n";
		foreach ($maze as $row) { echo '│', implode('', $row), '│', "\n"; }
		echo '┕', str_repeat('━', count($maze[0])), '┙', "\n";

		if (isset($__CLIOPTS['drawSearch'])) {
			usleep(isset($__CLIOPTS['sleep']) ? $__CLIOPTS['sleep'] : 25000);
		}
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
