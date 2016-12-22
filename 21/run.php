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

	$ops['swapPosition'] = function ($password, $x, $y) {
		$password[$x] ^= $password[$y] ^= $password[$x] ^= $password[$y];
		return $password;
	};

	$ops['swapLetter'] = function ($password, $x, $y) use ($ops) {
		$x = array_search($x, $password);
		$y = array_search($y, $password);
		return $ops['swapPosition']($password, $x, $y);
	};

	$ops['rotate'] = function ($password, $direction, $steps) {
		for ($i = 0; $i < $steps; $i++) {
			if ($direction == 'left') {
				array_push($password, array_shift($password));
			} else if ($direction == 'right') {
				array_unshift($password, array_pop($password));
			}
		}

		return $password;
	};

	$ops['rotateBasedOn'] = function ($password, $x) use ($ops) {
		$steps = array_search($x, $password);
		$steps++;
		if ($steps > 4) { $steps++; }
		return $ops['rotate']($password, 'right', $steps);
	};

	$ops['reverse'] = function ($password, $x, $y) {
		$mid = array_slice($password, $x, ($y - $x + 1));
		$mid = array_reverse($mid);
		array_splice($password, $x, ($y - $x + 1), $mid);
		return $password;
	};

	$ops['move'] = function ($password, $x, $y) {
		$letter = $password[$x];
		unset($password[$x]);
		$password = array_values($password);

		array_splice($password, $y, 0, $letter);
		return $password;
	};

	$reverseOps = $ops;

	$reverseOps['rotate'] = function ($password, $direction, $steps) use ($ops) {
		if ($direction == 'left') { $direction = 'right'; }
		else if ($direction == 'right') { $direction = 'left'; }
		return $ops['rotate']($password, $direction, $steps);
	};

	$reverseOps['rotateBasedOn'] = function ($password, $x, $reverse = false) use ($ops) {
		for ($i = 0; $i <= count($password); $i++) {
			$leftRotate = $ops['rotate']($password, 'left', $i);
			if ($ops['rotateBasedOn']($leftRotate, $x) == $password) {
				return $leftRotate;
			}
		}
	};

	$reverseOps['move'] = function ($password, $x, $y) use ($ops) {
		$x ^= $y ^= $x ^= $y;
		return $ops['move']($password, $x, $y);
	};

	function scramblePassword($password, $instructions, $ops) {
		global $__CLIOPTS;
		$password = str_split($password);

		if (isset($__CLIOPTS['visual'])) { echo '[', implode('', $password), ']'; }

		foreach ($instructions as $params) {
			$instr = $params[0];
			debugOut('[', implode(' ', $params), ': ', implode('', $password), ' => ');
			$params[0] = &$password;
			$password = call_user_func_array($ops[$instr], $params);
			debugOut(implode('', $password), ']', "\n");

			if (isset($__CLIOPTS['visual'])) {
				echo "\r", '[', implode('', $password), ']';
				usleep(50000);
			}
		}

		if (isset($__CLIOPTS['visual'])) { echo "\r"; }

		return implode('', $password);
	}

	$part1 = scramblePassword(isTest() ? 'abcde' : 'abcdefgh', $instructions, $ops);
	echo 'Part 1: ', $part1, "\n";

	if (!isTest()) {
		debugOut("\n\n");
		$part2 = scramblePassword('fbgdceah', array_reverse($instructions), $reverseOps);
		echo 'Part 2: ', $part2, "\n";
	}
