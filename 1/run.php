#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	$directions = explode(',', $input);

	function solveFor($directions, $dirs) {
		$x = $y = 0;
		$dir = 0;

		foreach ($directions as $d) {
			$d = trim($d);
			if (!preg_match('#^([RL])([0-9]+)#', $d, $m)) { continue; }

			$face = $m[1];
			$move = $m[2];

			if (isDebug()) {
				echo $d, "\n";
				echo '    Face: ', $face, ', Move: ', $move, "\n";
				echo '    Was: ', $dirs[$dir][0], '[', $x, ',', $y, ']', "\n";
			}

			// Re-orient
			if ($face == 'R') {
				$dir = ($dir + 1) % count($dirs);
			} else if ($face == 'L') {
				$dir = $dir - 1;
				if ($dir < 0) { $dir = count($dirs) - 1; }
			}

			$x += $dirs[$dir][1] * $move;
			$y += $dirs[$dir][2] * $move;

			if (isDebug()) {
				echo '    Now: ', $dirs[$dir][0], '[', $x, ',', $y, ']', "\n";
			}
		}

		if (isDebug()) { echo "\n"; }


		echo 'Final Location: ', $dirs[$dir][0], '[', $x, ',', $y, '] => Blocks away: ', (abs($x) + abs($y)), "\n";
	}

	// Array of directions and how to manipulate X/Y to move that direction.
	$dirs = [['N', 0, 1], ['E', 1, 0], ['S', 0, -1], ['W', -1, 0]];
	solveFor($directions, $dirs);
