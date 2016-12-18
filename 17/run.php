#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	$start = [0, 0];
	$end = [3, 3];

	$initialState = array('current' => $start, 'target' => $end, 'previous' => '', 'steps' => 0);

	// Excessive function to draw the current state.
	function drawState($state) {
		$roomMaxX = 4;
		$roomMaxY = 4;
		$maxX = ($roomMaxX * 2) + 1;
		$maxY = ($roomMaxY * 2) + 1;

		$c = ['E' => ' ', 'F' => '█', 'VR' => '╠', 'H' => '═', 'VL' => '╣', 'DR' => '╔', 'DL' => '╗', 'UR' => '╚', 'UL' => '╝', 'HD' => '╦', 'V' => '║', 'HU' => '╩', 'VH' => '╬'];

		// Empty Maze
		$maze = array_fill(0, $maxY, array_fill(0, $maxX, $c['F']));

		// Fill it in.
		for ($y = 0; $y < $maxY; $y++) {
			for ($x = 0; $x < $maxX; $x++) {
				// Skip if we have already set a character for this position.
				if ($maze[$y][$x] != $c['F']) { continue; }
				$char = $c['E'];

				// Corners
				if ($x == 0 && $y == 0) { $char = $c['DR']; }
				else if ($x == $maxX-1 && $y == $maxY-1) { $char = $c['UL']; }
				else if ($x == 0 && $y == $maxY-1) { $char = $c['UR']; }
				else if ($x == $maxX-1 && $y == 0) { $char = $c['DL']; }

				// Walls
				else if ($x == 0 && $y % 2 == 0) { $char = $c['VR']; }
				else if ($x == $maxX-1 && $y % 2 == 0) { $char = $c['VL']; }
				else if ($x % 2 == 0 && $y == 0) { $char = $c['HD']; }
				else if ($x % 2 == 0 && $y == $maxY-1) { $char = $c['HU']; }
				else if ($y % 2 == 0 && $x % 2 == 0) { $char = $c['VH']; }
				else if ($y % 2 == 0) { $char = $c['H']; }
				else if ($x % 2 == 0) { $char = $c['V']; }

				//
				else if ($x % 2 != 0 && $y % 2 != 0) {
					$loc = [($x - 1) / 2, ($y - 1) / 2];
					if ($loc == $state['current']) {
						$char = 'X';

						// DOORS
						$d = getDirections($state);
						if ($d['U']['passable']) { $maze[$y - 1][$x] = $c['E']; }
						if ($d['D']['passable']) { $maze[$y + 1][$x] = $c['E']; }
						if ($d['L']['passable']) { $maze[$y][$x - 1] = $c['E']; }
						if ($d['R']['passable']) { $maze[$y][$x + 1] = $c['E']; }
					} else if ($loc == $state['target']) { $char = 'V'; }

				}

				$maze[$y][$x] = $char;
			}
		}


		foreach ($maze as $row) { echo implode('', $row), "\n"; }
		echo 'Steps: ', $state['steps'], "\n";
		echo 'Path: ', $state['previous'], "\n";
		echo "\n";
	}

	function getDirections($state) {
		global $input;

		$code = str_split(substr(md5($input . $state['previous']), 0, 4));
		$pos = $state['current'];

		$passable = ['U' => ['passable' => (strpos('bcdef', $code[0]) !== FALSE && $pos[1] - 1 >= 0), 'pos' => [$pos[0], $pos[1] - 1]],
		             'D' => ['passable' => (strpos('bcdef', $code[1]) !== FALSE && $pos[1] + 1 <= 3), 'pos' => [$pos[0], $pos[1] + 1]],
		             'L' => ['passable' => (strpos('bcdef', $code[2]) !== FALSE && $pos[0] - 1 >= 0), 'pos' => [$pos[0] - 1, $pos[1]]],
		             'R' => ['passable' => (strpos('bcdef', $code[3]) !== FALSE && $pos[0] + 1 <= 3), 'pos' => [$pos[0] + 1, $pos[1]]]];

		return $passable;
	}

	function isFinished($state) {
		return ($state['current'] == $state['target']);
	}

	function getOptions($state) {
		$curX = $state['current'][0];
		$curY = $state['current'][1];

		$options = [];
		$directions = getDirections($state);
		foreach (['U', 'D', 'L', 'R'] as $direction) {
			if ($directions[$direction]['passable']) {
				$newState = $state;
				$newState['previous'] .= $direction;
				$newState['current'] = $directions[$direction]['pos'];
				$newState['steps']++;
				$options[] = $newState;
			}
		}

		return $options;
	}

	function solveMaze($beginState, $longest = false) {
		$states = [$beginState];

		$finalState = FALSE;

		while (count($states) > 0) {
			$state = array_shift($states);

			if (isFinished($state)) {
				debugOut('Finished With: [', $state['steps'], '] {', implode(', ', $state['current']), '}', "\n");
				if (isDebug()) { drawState($state); }
				if ($longest) {
					if ($state['steps'] > $finalState['steps']) {
						$finalState = $state;
					}
					continue;
				} else {
					$finalState = $state;
					break;
				}
			}

			$options = getOptions($state);
			foreach ($options as $opt) {
				$states[] = $opt;
			}
		}

		return $finalState;
	}

	$part1 = solveMaze($initialState, false);
	echo 'Part 1: ', $part1['previous'], "\n";

	$part2 = solveMaze($initialState, true);
	echo 'Part 2: ', strlen($part2['previous']), "\n";
