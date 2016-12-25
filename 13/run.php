#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	require_once(dirname(__FILE__) . '/../common/pathfinder.php');
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


	$pathFinder = new PathFinder(null, $start, $end);
	$pathFinder->setHook('isAccessible', function($state, $x, $y) { return !isWall($x, $y); });
	$pathFinder->setHook('isValidLocation', function($state, $x, $y) {
		list($curX, $curY) = $state['current'];
		if ($x < 0 || $y < 0) { return FALSE; } // Ignore Negative
		if ($x != $curX && $y != $curY) { return FALSE; } // Ignore Corners
		if ($x == $curX && $y == $curY) { return FALSE; } // Ignore Current
		return true;
	});

	if (isDebug() || isset($__CLIOPTS['drawSearch'])) {
		$redraw = isset($__CLIOPTS['drawSearch']);

		$pathFinder->setHook('solveStartState', function($state) { drawState($state, [], false); });
		$pathFinder->setHook('solveFinishedState', function($state, $visted) use ($redraw) { drawState($state, $visted, $redraw); });
		$pathFinder->setHook('solveNextState', function($state, $visted) use ($redraw) { drawState($state, $visted, $redraw); });
	}

	if (!isset($__CLIOPTS['2'])) {
		list($part1, $_) = $pathFinder->solveMaze();
		echo 'Part 1: ', $part1['steps'], "\n";
	}


	if (!isset($__CLIOPTS['1'])) {
		$limit = isset($__CLIOPTS['limit']) ? $__CLIOPTS['limit'] : 50;
		list($_, $part2) = $pathFinder->solveMaze($limit);
		echo 'Part 2: ', count($part2), "\n";
	}
