#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	$start = [0, 0];
	$end = [3, 3];

	$initialState = array('current' => $start, 'target' => $end, 'previous' => '', 'steps' => 0);

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
