#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	$i = 0;
	$length = 8;
	$password1 = '';
	$password2 = array_fill(0, $length, ' ');
	while (strlen($password1) < $length || in_array(' ', $password2)) {
		$test = md5($input . $i++);
		if (startswith($test, '00000')) {
			$changed = false;
			if (strlen($password1) < $length) { $password1 .= $test{5}; $changed = true; }
			if (isset($password2[$test{5}]) && $password2[$test{5}] == ' ') { $password2[$test{5}] = $test{6}; $changed = true; }

			if ($changed) { debugOut(sprintf('%10s - 1:[%'.$length.'s] 2:[%'.$length.'s] - %s', $i, $password1, implode('', $password2), $test), "\n"); }
		}
	}

	debugOut("\n");
	echo 'Part 1: ', $password1, "\n";
	echo 'Part 2: ', implode('', $password2), "\n";
