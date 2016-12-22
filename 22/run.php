#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$grid = array();
	foreach ($input as $details) {
		if (preg_match('#/dev/grid/node-x([0-9]+)-y([0-9]+)\s+([0-9]+)T\s+([0-9]+)T\s+([0-9]+)T\s+([0-9]+)%#SADi', $details, $m)) {
			list($all, $x, $y, $size, $used, $avail, $percent) = $m;

			if (!isset($grid[$y])) { $grid[$y] = array(); }
			$grid[$y][$x] = ['size' => $size, 'used' => $used, 'avail' => $avail, 'percent' => $percent];
		}
	}

	/**
	 * Get nodes from the grid with the required free space.
	 *
	 * @param $grid Grid to look at
	 * @param $freeSpace Minimum required free space.
	 * @param $exclude Node to exclude
	 * @return Array of [X, Y] pairs.
	 */
	function getPossibleNodes($grid, $freeSpace = 0, $exclude = [-1, -1]) {
		$result = array();
		foreach ($grid as $y => $xs) {
			foreach ($xs as $x => $node) {
				if ($x == $exclude[0] && $y == $exclude[1]) { continue; }
				if ($node['avail'] >= $freeSpace) {
					$result[] = [$x, $y];
				}
			}
		}
		return $result;
	}

	/**
	 * Get the viable nodes count.
	 *
	 * @param $grid Grid to look at
	 * @return Count of viable nodes.
	 */
	function getViableNodesCount($grid) {
		$viableCount = 0;

		foreach ($grid as $y => $xs) {
			foreach ($xs as $x => $node) {
				if ($node['used'] > 0) {
					$viableCount += count(getPossibleNodes($grid, $node['used'], [$x, $y]));
				}
			}
		}

		return $viableCount;
	}

	echo 'Part 1: ', getViableNodesCount($grid), "\n";
