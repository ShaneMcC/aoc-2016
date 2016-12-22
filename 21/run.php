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

	function swapPosition($password, $x, $y) {
		$password[$x] ^= $password[$y] ^= $password[$x] ^= $password[$y];
		return $password;
	}

	function swapLetter($password, $x, $y) {
		$x = array_search($x, $password);
		$y = array_search($y, $password);
		return swapPosition($password, $x, $y);
	}

	function rotate($password, $direction, $steps, $reverse = false) {
		if ($reverse && $direction == 'left') { $direction = 'right'; }
		else if ($reverse && $direction == 'right') { $direction = 'left'; }

		for ($i = 0; $i < $steps; $i++) {
			if ($direction == 'left') {
				array_push($password, array_shift($password));
			} else if ($direction == 'right') {
				array_unshift($password, array_pop($password));
			}
		}

		return $password;
	}

	function rotateBasedOn($password, $x, $reverse = false) {
		if ($reverse) {
			for ($i = 0; $i <= count($password); $i++) {
				$leftRotate = rotate($password, 'left', $i);
				if (rotateBasedOn($leftRotate, $x) == $password) {
					return $leftRotate;
				}
			}
		} else {
			$steps = array_search($x, $password);
			$steps++;
			if ($steps > 4) { $steps++; }
			return rotate($password, 'right', $steps, $reverse);
		}
	}

	function reverse($password, $x, $y) {
		$mid = array_slice($password, $x, ($y - $x + 1));
		$mid = array_reverse($mid);
		array_splice($password, $x, ($y - $x + 1), $mid);
		return $password;
	}

	function move($password, $x, $y, $reverse = false) {
		if ($reverse) { $x ^= $y ^= $x ^= $y; }

		$letter = $password[$x];
		unset($password[$x]);
		$password = array_values($password);

		array_splice($password, $y, 0, $letter);
		return $password;
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
			$password = call_user_func_array($instr, $params);
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
