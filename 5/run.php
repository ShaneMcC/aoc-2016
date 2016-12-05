#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	$i = 0;
	$password1 = '';
	while (strlen($password1) < 8) {
		$test = md5($input . $i++);
		if (startswith($test, '00000')) {
			$password1 .= $test{5};
			debugOut(sprintf('%10s - [%8s] - %s', $i, $password1, $test), "\n");
		}
	}

	echo 'Part 1: ', $password1, "\n";
