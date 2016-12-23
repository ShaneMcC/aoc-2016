<?php
	$__CLI['long'] = ['drawSearch'];

	function drawState ($state, $redraw = true) {
		global $__CLIOPTS;

		$grid = $state['grid'];

		if (!isset($__CLIOPTS['drawSearch'])) { $redraw = false; }

		if ($redraw) { echo "\033[" . (count($grid) + 2) . "A"; }

		list($tX, $tY) = $state['target'];

		foreach ($state['previous'] as $prev) {
			list($pX, $pY) = $prev;
			$grid[$pY][$pX]['colour'] = "\033[0;34m";
		}

		echo '┍', str_repeat('━', count($grid[0])), '┑', "\n";

		foreach ($grid as $y => $row) {
			echo '│';
			foreach ($row as $x => $node) {
				if (!isset($node['colour'])) { $node['colour'] = "\033[0m"; }

				echo $node['colour'];

				if ($x == $tX && $y == $tY) {
					echo 'G';
				} else if ($node['used'] == 0) {
					echo '_';
				} else if (count(getPossibleNodes($grid, $node['used'], [$x, $y])) == 0) {
					echo '#';
				} else {
					echo '.';
				}

				echo "\033[0m";
			}
			echo '│', "\n";
		}

		echo '┕', str_repeat('━', count($grid[0])), '┙', "\n";

	}
