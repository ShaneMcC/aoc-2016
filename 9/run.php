#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();


	function decompress($line) {
		$newLine = '';
		$brackets = FALSE;
		$bracketsData = '';
		for ($i = 0; $i < strlen($line); $i++) {
			if ($line{$i} == '(') {
				$bracketsData = '';
				$brackets = TRUE;
			} else if ($line{$i} == ')') {
				$brackets = FALSE;
				preg_match('#([0-9]+)x([0-9]+)#', $bracketsData, $m);
				list($all, $chars, $times) = $m;
				$newLine .= str_repeat(substr($line, $i + 1, $chars), $times);
				$i += $chars;
			} else if ($brackets) {
				$bracketsData .= $line{$i};
			} else {
				$newLine .= $line{$i};
			}
		}

		echo $line, ' => ', trim($newLine), ' {', strlen($newLine), '}', "\n";
	}

	foreach ($input as $line) {
		decompress($line);
	}
