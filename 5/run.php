#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	$i = 0;
	$password1 = '';
	$password2 = str_split('        ');
	while (strlen($password1) < 8 || in_array(' ', $password2)) {
		$test = md5($input . $i++);
		if (startswith($test, '00000')) {
			if (strlen($password1) < 8) {
				$password1 .= $test{5};
			}
			if ($test{5} >= 0 && $test{5} < 8 && isset($password2[$test{5}]) && $password2[$test{5}] == ' ') {
				$password2[$test{5}] = $test{6};
			}
			debugOut(sprintf('%10s - 1:[%8s] 2:[%8s] - %s', $i, $password1, implode('', $password2), $test), "\n");
		}
	}

	debugOut("\n");
	echo 'Part 1: ', $password1, "\n";
	echo 'Part 2: ', implode('', $password2), "\n";
