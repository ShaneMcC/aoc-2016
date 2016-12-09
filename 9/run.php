#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();


	function decompressV1($line) {
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

		return $newLine;
	}

	function decompressV2Size($line) {
		$count = 0;
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
				$count += (decompressV2Size(substr($line, $i + 1, $chars)) * $times);
				$i += $chars;
			} else if ($brackets) {
				$bracketsData .= $line{$i};
			} else {
				$count++;
			}
		}

		return $count;
	}

	foreach ($input as $line) {
		// $newLine = decompressV2($line);
		// echo $line, ' => ', trim($newLine), ' {', strlen($newLine), '}', "\n";
		//
		echo decompressV2Size($line);
	}
