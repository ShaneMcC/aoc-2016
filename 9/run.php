#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	function decompress($line, $version = 1, $sizeOnly = true) {
		$newLine = '';
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
				if ($sizeOnly) {
					$compressedSize = ($version == 1) ? strlen(substr($line, $i + 1, $chars)) : decompress(substr($line, $i + 1, $chars), $version, true);
					$count += $compressedSize * $times;
				} else {
					$bit = ($version == 1) ? substr($line, $i + 1, $chars) : decompress(substr($line, $i + 1, $chars), $version, false);
					$newLine .= str_repeat($bit, $times);
				}
				$i += $chars;
			} else if ($brackets) {
				$bracketsData .= $line{$i};
			} else {
				if ($sizeOnly) { $count++; } else { $newLine .= $line{$i}; }
			}
		}

		return ($sizeOnly) ? $count : $newLine;
	}

	echo 'Part 1: ', decompress($input, 1, !isDebug()), "\n";
	echo 'Part 2: ', decompress($input, 2, !isDebug()), "\n";
