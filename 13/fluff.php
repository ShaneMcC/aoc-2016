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

	function setMazeChar(&$maze, $x, $y, $char) {
		if (isset($maze[$y][$x])) { $maze[$y][$x] = $char; }
	}

	function drawState($state, $visted, $redraw = true) {
		global $__CLIOPTS;

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
