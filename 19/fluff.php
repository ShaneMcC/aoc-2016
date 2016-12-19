<?php
	$__CLI['long'] = ['brute', 'limit:', 'start:'];

	function doBruteForce() {
		echo ' elves | part1 | part2 ', "\n";
		echo '=======|=======|=======', "\n";

		$limit = isset($__CLIOPTS['limit']) ? $__CLIOPTS['limit'] : 100;
		$start = isset($__CLIOPTS['start']) ? $__CLIOPTS['start'] : 1;

		for ($i = $start; $i <= $limit; $i++) {
			$part1 = sprintf('%3s', bruteforcePart1($i));
			$part2 = sprintf('%3s', bruteforcePart2($i));
			if ($part1 == '  1') { $part1 = "\033[1;32m" .  $part1 . "\033[0m"; }
			if ($part2 == '  1') { $part2 = "\033[1;32m" .  $part2 . "\033[0m"; }

			echo sprintf('   %3s |  %s  |  %s', $i, $part1, $part2), "\n";
		}
		die();
	}

	function bruteforcePart1($count) {
		$elves = [];
		for ($i = 1; $i <= $count; $i++) { $elves[] = $i; }
		while (count($elves) > 1) {
			$has = array_shift($elves);
			$none = array_shift($elves);
			array_push($elves, $has);
		}
		return array_shift($elves);
	}

	function bruteforcePart2($count) {
		$elves = [];
		for ($i = 1; $i <= $count; $i++) { $elves[] = $i; }
		while (count($elves) > 1) {
			$newElves = [];
			$j = floor(count($elves) / 2) - 1;
			$current = array_shift($elves);
			$elves = array_merge(array_slice($elves, 0, $j), array_slice($elves, $j + 1), [$current]);
			debugOut('Remaining: ', implode(', ', $elves));
		}
		return array_shift($elves);
	}

