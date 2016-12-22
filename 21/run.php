#!/usr/bin/php
<?php
	$__CLI['long'] = ['visual'];
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$instructions = array();
	foreach ($input as $details) {
		if (preg_match('#swap position ([0-9]+) with position ([0-9]+)#SADi', $details, $m)) {
			list($all, $x, $y) = $m;
			$instructions[] = ['swapPosition', $x, $y];
		} else if (preg_match('#swap letter ([a-z]+) with letter ([a-z]+)#SADi', $details, $m)) {
			list($all, $x, $y) = $m;
			$instructions[] = ['swapLetter', $x, $y];
		} else if (preg_match('#rotate (left|right) ([0-9]+) steps?#SADi', $details, $m)) {
			list($all, $direction, $steps) = $m;
			$instructions[] = ['rotate', $direction, $steps];
		} else if (preg_match('#rotate based on position of letter ([a-z]+)#SADi', $details, $m)) {
			list($all, $x) = $m;
			$instructions[] = ['rotateBasedOn', $x];
		} else if (preg_match('#reverse positions ([0-9]+) through ([0-9]+)#SADi', $details, $m)) {
			list($all, $x, $y) = $m;
			$instructions[] = ['reverse', $x, $y];
		} else if (preg_match('#move position ([0-9]+) to position ([0-9]+)#SADi', $details, $m)) {
			list($all, $x, $y) = $m;
			$instructions[] = ['move', $x, $y];
		}
	}

	function swapPosition(&$password, $x, $y, $reverse = false) {
		$password[$x] ^= $password[$y] ^= $password[$x] ^= $password[$y];
	}

	function swapLetter(&$password, $x, $y, $reverse = false) {
		$x = array_search($x, $password);
		$y = array_search($y, $password);
		swapPosition($password, $x, $y, $reverse);
	}

	function rotate(&$password, $direction, $steps, $reverse = false) {
		if ($reverse && $direction == 'left') { $direction = 'right'; }
		else if ($reverse && $direction == 'right') { $direction = 'left'; }

		for ($i = 0; $i < $steps; $i++) {
			if ($direction == 'left') {
				array_push($password, array_shift($password));
			} else if ($direction == 'right') {
				array_unshift($password, array_pop($password));
			}
		}
	}

	function rotateBasedOn(&$password, $x, $reverse = false) {
		$steps = array_search($x, $password);
		if ($reverse) {
			// Array of final position => numberOfRightRotations.
			// This only works for length-8 passwords, for other lengths (eg 5)
			// the reversal is non-deterministic:
			//     "abcde" rotate by "c" => "cdeab"
			//     "abcde" rotate by "e" => "eabcd"
			// In both cases, the final position is "0" so when reversing we
			// have no idea if the original position was index 2 or 4
			//
			// I don't like this, but it lets me solve the day, so...
			$stepPos = [1 => 1, 3 => 2, 5 => 3, 7 => 4, 2 => 6, 4 => 7, 6 => 8, 0 => 9];
			$steps = $stepPos[$steps];
		} else {
			$steps++;
			if ($steps > 4) { $steps++; }
		}

		rotate($password, 'right', $steps, $reverse);
	}

	function reverse(&$password, $x, $y, $reverse = false) {
		$mid = array_slice($password, $x, ($y - $x + 1));
		$mid = array_reverse($mid);
		array_splice($password, $x, ($y - $x + 1), $mid);
	}

	function move(&$password, $x, $y, $reverse = false) {
		if ($reverse) { $x ^= $y ^= $x ^= $y; }

		$letter = $password[$x];
		unset($password[$x]);
		$password = array_values($password);

		array_splice($password, $y, 0, $letter);
	}

	function scramblePassword($password, $instructions, $reverse = false) {
		global $__CLIOPTS;
		$password = str_split($password);

		if ($reverse) { $instructions = array_reverse($instructions); }
		if (isset($__CLIOPTS['visual'])) { echo '[', implode('', $password), ']'; }

		foreach ($instructions as $params) {
			$instr = $params[0];
			debugOut('[', implode(' ', $params), ': ', implode('', $password), ' => ');
			$params[0] = &$password;
			$params[] = $reverse;
			call_user_func_array($instr, $params);
			debugOut(implode('', $password), ']', "\n");

			if (isset($__CLIOPTS['visual'])) {
				echo "\r", '[', implode('', $password), ']';
				usleep(50000);
			}
		}

		if (isset($__CLIOPTS['visual'])) { echo "\r"; }

		return implode('', $password);
	}

	$part1 = scramblePassword(isTest() ? 'abcde' : 'abcdefgh', $instructions);
	echo 'Part 1: ', $part1, "\n";

	if (!isTest()) {
		debugOut("\n\n");
		$part2 = scramblePassword('fbgdceah', $instructions, true);
		echo 'Part 2: ', $part2, "\n";
	}
