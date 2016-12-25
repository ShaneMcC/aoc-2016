#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	require_once(dirname(__FILE__) . '/../22/pathfinder.php');
	$input = getInputLines();

	$grid = array();
	foreach ($input as $row) {
		$grid[] = str_split($row);
	}

	function findPosition($grid, $number) {
		foreach (yieldXY(0, 0, count($grid[0]) - 1, count($grid) - 1) as $x => $y) {
			if ($grid[$y][$x] == (string)$number) {
				return [$x, $y];
			}
		}
		return FALSE;
	}

	$numbers = [];
	foreach (yieldXY(0, 0, count($grid[0]) - 1, count($grid) - 1) as $x => $y) {
		if ($grid[$y][$x] != '.' && $grid[$y][$x] != '#') {
			$numbers[] = $grid[$y][$x];
		}
	}
	sort($numbers);


	function getPathLength($grid, $begin, $end) {
		$start = findPosition($grid, $begin);
		$target = findPosition($grid, $end);

		$isAccessible = function($state, $x, $y) { return $state['grid'][$y][$x] != '#'; };

		$pathFinder = new PathFinder($grid, $start, $target, $isAccessible);
		$path = $pathFinder->solveMaze();

		return $path[0]['steps'];
	}

	function getNodeLengths($grid, $numbers) {
		global $__NLCACHE;
		$numbers = array_unique($numbers);
		sort($numbers);
		$hash = crc32(serialize($numbers));

		if (isset($__NLCACHE[$hash])) { return $__NLCACHE[$hash]; }

		$lengths = array();
		$sets = getAllSets($numbers, 2);
		foreach ($sets as $set) {
			if (count($set) < 2) { continue; }
			sort($set);
			list($start, $end) = $set;
			$lengths[$start . '-' . $end] = getPathLength($grid, $start, $end);
		}

		$__NLCACHE[$hash] = $lengths;
		return $lengths;
	}

	function getLength($grid, $path, $maxLength = -1) {
		$lengths = getNodeLengths($grid, $path);

		$length = 0;
		for ($i = 0; $i < count($path) - 1; $i++) {
			$pair = [$path[$i], $path[$i + 1]];
			sort($pair);
			$length += $lengths[$pair[0] . '-' . $pair[1]];
			if ($maxLength != -1 && $length > $maxLength) { return FALSE; }
		}
		return $length;
	}

	function getShortest($grid, $start, $numbers, $backToStart = false) {
		$bestLength = -1;
		$bestPath = NULL;
		foreach (getPermutations($numbers) as $path) {
			$path = $backToStart ? array_merge([$start], $path, [$start]) : array_merge([$start], $path);
			$length = getLength($grid, $path, $bestLength);

			if (($length !== FALSE) && ($bestLength == -1 || $bestLength > $length)) {
				$bestPath = $path;
				$bestLength = $length;
			}
		}

		return [$bestPath, $bestLength];
	}

	// Pop 0 off the start of the list as we always start there.
	array_shift($numbers);
	$part1 = getShortest($grid, '0', $numbers);
	echo 'Part 1: ', implode(' -> ', $part1[0]), ' => ', $part1[1], "\n";

	// Now with 0 at the end as well.
	$part2 = getShortest($grid, '0', $numbers, true);
	echo 'Part 2: ', implode(' -> ', $part2[0]), ' => ', $part2[1], "\n";
