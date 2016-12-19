<?php

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
