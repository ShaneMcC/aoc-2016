#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$floorNumbers = ['first' => 1, 'second' => 2, 'third' => 3, 'fourth' => 4];
	$knownElements = ['polonium' => 'Po', 'thulium' => 'Tm', 'promethium' => 'Pm', 'ruthenium' => 'Ru', 'cobalt' => 'Co', 'hydrogen' => 'H', 'lithium' => 'Li', 'elerium' => 'El', 'dilithium' => 'Di'];
	$initialState = ['floors' => [], 'elevator' => '1', 'count' => 0];

	function addItem(&$state, $element, $type, $floor) {
		global $knownElements;

		$code = ($knownElements[$element] ?: $element) . ($type == ' generator' ? 'G' : 'M');
		$state['floors'][$floor][] = $code;
	}

	foreach ($input as $details) {
		preg_match('#^The ([^\s]+) floor contains (.*)#SADi', $details, $m);
		list($all, $floor, $contents) = $m;
		$initialState['floors'][$floorNumbers[$floor]] = array();

		if (preg_match_all('#([a-z]+)(-compatible microchip| generator)#', $contents, $m)) {
			for ($i = 0; $i < count($m[0]); $i++) {
				addItem($initialState, $m[1][$i], $m[2][$i], $floorNumbers[$floor]);
			}
		}
	}

	function getItemLocations($state) {
		$allItems = [];

		foreach ($state['floors'] as $floor => $contents) {
			foreach ($contents as $item) {
				$allItems[$item] = $floor;
			}
		}

		// Sort the item columns.
		ksort($allItems);
		// Add elevator to the start of the items list to make it the first column
		$allItems = array_merge(['E ' => $state['elevator']], $allItems);

		return $allItems;
	}

	// Output what is where, ensuring that items always stay in the
	// same column no matter what floor they move to, with the elevator first.
	function showFloors($state) {
		$allItems = getItemLocations($state);

		// Print each floor.
		foreach (array_reverse($state['floors'], true) as $floor => $contents) {
			$floorContents = [];

			echo 'F', $floor, ' ';
			foreach ($allItems as $item => $floorNum) {
				echo ' ', sprintf('%3s', ($floorNum == $floor ? $item : ' . ')), ' ';
				if ($floorNum == $floor) { $floorContents[] = $item; }
			}
			echo isSafe($floorContents) ? '     [  SAFE]' : '     [UNSAFE]';
			echo "\n";
		}
	}

	function findFloor($state, $find) {
		foreach ($state['floors'] as $floor => $items) {
			foreach ($items as $item) {
				if ($item == $find) {
					return $floor;
				}
			}
		}
	}

	// Hash the state such that similar states end up hashing the same.
	// Based on: https://github.com/csmith/aoc-2016/blob/master/11.py
	function getHash($state) {
		$types = [];
		foreach ($state['floors'] as $items) {
			foreach ($items as $item) {
				preg_match('#^(.*)(G|M)$#', $item, $m);
				$types[$m[1]] = (findFloor($state, $m[1].'G') * 4) + (findFloor($state, $m[1].'M'));
			}
		}
		asort($types);

		$map = [];
		$i = 0;
		foreach (array_keys($types) as $type) {
			$map[$type.'G'] = $i . 'G';
			$map[$type.'M'] = $i . 'M';
			$i++;
		}

		$hash = [];
		foreach ($state['floors'] as $floor => $items) {
			$str = [];
			foreach ($items as $item) {
				$str[] = $map[$item];
			}
			if ($state['elevator'] == $floor) { $str[] = 'E'; }
			sort($str);
			$hash[] = implode('', $str);
		}

		return implode('|', $hash);
	}

	function isFinished($state) {
		if ($state['elevator'] != count($state['floors'])) { return FALSE; }

		// If there are any items not on the top floor then return false.
		foreach ($state['floors'] as $floor => $contents) {
			if ($floor == count($state['floors'])) { continue; }
			foreach ($contents as $item) {
				return false;
			}
		}

		return true;
	}

	// What can we do right now
	function getOptions($state) {
		$currentFloor = $state['elevator'];

		$options = getAllSets($state['floors'][$currentFloor], 2);

		$result = [];
		foreach ($options as $opt) {
			if (count($opt) == 0) { continue; }
			foreach ([$currentFloor + 1, $currentFloor - 1] as $floor) {
				if (!isset($state['floors'][$floor])) { continue; };
				$newFloor = $state['floors'][$floor];
				$oldFloor = $state['floors'][$currentFloor];
				foreach ($opt as $o) {
					$oldFloor = array_diff($oldFloor, [$o]);
					$newFloor[] = $o;
				}

				if (isSafe($newFloor) && isSafe($oldFloor)) {
					$newState = $state;
					$newState['floors'][$floor] = $newFloor;
					$newState['floors'][$currentFloor] = $oldFloor;
					$newState['elevator'] = $floor;
					$newState['count']++;
					$result[] = $newState;
				}
			}
		}

		return $result;
	}

	function isSafe($floorContents) {
		$generators = [];
		$chips = [];

		foreach ($floorContents as $item) {
			if (preg_match('#^(.*)(G|M)$#', $item, $m)) {
				if ($m[2] == 'M') { $chips[] = $m[1]; } elseif ($m[2] == 'G') { $generators[] = $m[1]; }
			}
		}

		$unsafeChips = [];

		// Check which chips are safely-connected to their generators.
		foreach ($chips as $c) {
			if (!in_array($c, $generators)) { $unsafeChips[] = $c; }
		}

		// We are safe if there are no generators or no unsafechips.
		return count($generators) == 0 || count($unsafeChips) == 0;
	}

	function run($beginState) {
		$visted = [getHash($beginState)];
		$states = [$beginState];

		while (count($states) > 0) {
			$state = array_shift($states);
			$thisHash = getHash($state);

			if (isFinished($state)) {
				debugOut('Finished With: [', $state['count'], '] {', $thisHash, '}', "\n");
				return $state;
			} else {
				debugOut('Testing: [', $state['count'], '] {', $thisHash, '}', "\n");
			}

			$options = getOptions($state);
			debugOut("\t", 'Found Options: ', count($options), "\n");

			foreach ($options as $opt) {
				$optHash = getHash($opt);
				if (!in_array($optHash, $visted)) {
					$visted[] = $optHash;
					$states[] = $opt;

					debugOut("\t\t", 'New Option: ', $optHash, "\n");
				}
			}
		}

		die('Unable to continue, no answer found.' . "\n");
	}

	$part1State = $initialState;
	$part1 = run($part1State);

	if (!isTest()) {
		$part2State = $initialState;
		addItem($part2State, 'elerium', ' generator', 1);
		addItem($part2State, 'elerium', '-compatible microchip', 1);
		addItem($part2State, 'dilithium', ' generator', 1);
		addItem($part2State, 'dilithium', '-compatible microchip', 1);
		$part2 = run($part2State);
	}

	echo 'Part 1: ', $part1['count'], "\n";
	if (!isTest()) { echo 'Part 2: ', $part2['count'], "\n"; }
