#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	$directions = explode(',', $input);

	$dirs = [['N', 0, 1], ['E', 1, 0], ['S', 0, -1], ['W', -1, 0]];
	$dir = $x = $y = 0;
	$visted = [];
	$firstTwice = null;

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

		$dir = wrapmod($dir + ($face == 'R' ? '1' : '-1'), count($dirs));

		for ($i = 0; $i < $move; $i++) {
			$x += $dirs[$dir][1];
			$y += $dirs[$dir][2];

			if (isset($visted[$x . ',' . $y]) && $firstTwice == null) {
				$firstTwice = [$x, $y];
			} else {
				$visted[$x . ',' . $y] = true;
			}
		}

		if (isDebug()) { echo '    Now: ', $dirs[$dir][0], '[', $x, ',', $y, ']', "\n"; }
	}

	if (isDebug()) { echo "\n"; }
	echo 'Final Location: ', $dirs[$dir][0], '[', $x, ',', $y, '] => Blocks away: ', (abs($x) + abs($y)), "\n";

	if ($firstTwice !== null) {
		list($x, $y) = $firstTwice;
		echo 'First Duplicate Visit: [', $x, ',', $y, '] => Blocks away: ', (abs($x) + abs($y)), "\n";
	}
