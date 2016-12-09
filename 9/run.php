#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	function decompress($line, $version = 1, $sizeOnly = true) {
		$newLine = '';
		$count = 0;
		for ($i = 0; $i < strlen($line); $i++) {
			if ($line{$i} == '(') {
				$cur = $i;
				$i = strpos($line, ')', $i);
				$bracketsData = substr($line, $cur, $i - $cur);

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
			} else {
				if ($sizeOnly) { $count++; } else { $newLine .= $line{$i}; }
			}
		}

		return ($sizeOnly) ? $count : $newLine;
	}

	if (isDebug()) {
		echo 'Input: ', $input, "\n";
		echo 'Part 1: {', decompress($input, 1, true), '} => ', decompress($input, 1, false), "\n";
		echo 'Part 2: {', decompress($input, 2, true), '} => ', decompress($input, 2, false), "\n";
	} else {
		echo 'Part 1: ', decompress($input, 1, true), "\n";
		echo 'Part 2: ', decompress($input, 2, true), "\n";
	}
