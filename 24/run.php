#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	require_once(dirname(__FILE__) . '/../22/pathfinder.php');
	$grid = array();
	foreach (getInputLines() as $row) { $grid[] = str_split($row); }

	/**
	 * Cleaning Bot!
	 */
	class CleaningBot {
		/* Grid for bot. */
		var $grid = [];

		/* Numbers and their locations. */
		var $numbers = [];

		/* Known lengths between number pairs. */
		var $lengths = [];

		/**
		 * Create a new bot with the given grid.
		 *
		 * @param $grid Grid for bot to live in.
		 */
		function __construct($grid) {
			$this->grid = $grid;

			foreach (yieldXY(0, 0, count($grid[0]) - 1, count($grid) - 1) as $x => $y) {
				if ($grid[$y][$x] != '.' && $grid[$y][$x] != '#') {
					$this->numbers[$grid[$y][$x]] = [$x, $y];
				}
			}
			ksort($this->numbers);
		}

		/**
		 * Get the known numbers on this grid.
		 *
		 * @return Grid numbers.
		 */
		function getNumbers() {
			return array_keys($this->numbers);
		}

		/**
		 * Find the shortest length between 2 nodes.
		 *
		 * @param $begin Begin node.
		 * @param $end End node.
		 * @return The length between the nodes.
		 */
		function getLength($begin, $end) {
			$start = $this->numbers[$begin];
			$target = $this->numbers[$end];

			$isAccessible = function($state, $x, $y) { return $state['grid'][$y][$x] != '#'; };
			$pathFinder = new PathFinder($this->grid, $start, $target, $isAccessible);
			$path = $pathFinder->solveMaze();

			return $path[0]['steps'];
		}

		/**
		 * Get the legnths between all the nodes.
		 *
		 * If the lengths have not yet been worked out, we calculate them here.
		 *
		 * @return Array of lengths between nodes.
		 */
		function getNodeLengths() {
			if (count($this->lengths) == 0) {
				$sets = getAllSets(array_keys($this->numbers), 2);
				foreach ($sets as $set) {
					if (count($set) < 2) { continue; }
					list($start, $end) = $set;
					$length = $this->getLength($start, $end);
					$this->lengths[$start . '-' . $end] = $this->lengths[$end . '-' . $start] = $length;
				}
			}

			return $this->lengths;
		}

		/**
		 * Get the length of the given path.
		 *
		 * @param $path Path to follow as an array of nodes in order.
		 * @param $maxLength Give up if length is greater than this.
		 * @return Length of the path, or FALSE If longer than $maxLength
		 */
		function getPathLength($path, $maxLength = -1) {
			$lengths = $this->getNodeLengths();

			$length = 0;
			for ($i = 0; $i < count($path) - 1; $i++) {
				$pair = [$path[$i], $path[$i + 1]];
				$length += $lengths[$pair[0] . '-' . $pair[1]];
				if ($maxLength != -1 && $length > $maxLength) { return FALSE; }
			}
			return $length;
		}

		/**
		 * Find the shortest path.
		 *
		 * @param $start Node to start at.
		 * @param $numbers Numbers to vist (excluding $start);
		 * @param $backToStart [Default: false] Should we return back to the
		 *                     start after visiting all the numbers?
		 * @return Array of [$path, $length] for the best path.
		 */
		function getShortest($start, $numbers, $backToStart = false) {
			$bestLength = -1;
			$bestPath = NULL;
			foreach (getPermutations($numbers) as $path) {
				$path = $backToStart ? array_merge([$start], $path, [$start]) : array_merge([$start], $path);
				$length = $this->getPathLength($path, $bestLength);

				if (($length !== FALSE) && ($bestLength == -1 || $bestLength > $length)) {
					$bestPath = $path;
					$bestLength = $length;
				}
			}

			return [$bestPath, $bestLength];
		}
	}

	// Create cleaning box.
	$bot = new CleaningBot($grid);

	// Get the non-0 nodes
	$numbers = $bot->getNumbers();
	array_shift($numbers);

	// Path from 0, collecting all nodes.
	$part1 = $bot->getShortest('0', $numbers);
	echo 'Part 1: ', implode(' -> ', $part1[0]), ' => ', $part1[1], "\n";

	// Path from 0, collecting all nodes, then back to 0
	$part2 = $bot->getShortest('0', $numbers, true);
	echo 'Part 2: ', implode(' -> ', $part2[0]), ' => ', $part2[1], "\n";
