#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	function getHash($str, $stretchedCount = 0) {
		global $__HASHES;

		if (!isset($__HASHES[$stretchedCount . '_' . $str])) {
			$hash = md5($str);
			for ($i = 0; $i < $stretchedCount; $i++) { $hash = md5($hash); }
			$__HASHES[$stretchedCount . '_' . $str] = $hash;
		}

		return $__HASHES[$stretchedCount . '_' . $str];
	}

	function validHash($input, $position, $stretchedCount = 0) {
		$hash = getHash($input . $position, $stretchedCount);
		if (preg_match('#(.)\1\1#', $hash, $m)) {
			$needle = str_repeat($m[1], 5);
			for ($i = $position + 1; $i < $position + 1000; $i++) {
				if (strpos(getHash($input . $i, $stretchedCount), $needle) !== FALSE) {
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	function getLastHashIndex($input, $count = '64', $stretchedCount = 0) {
		$validHashes = array();
		$i = 0;
		while (true) {
			if (validHash($input, $i, $stretchedCount)) {
				$hash = getHash($input . $i, $stretchedCount);
				$validHashes[] = $hash;
				debugOut('Found valid hash ', count($validHashes), ' at ', $i, ': ', $hash, "\n");
			}
			if (count($validHashes) == $count) { break; }
			$i++;
		}

		return $i;
	}

	$part1 = getLastHashIndex($input, 64);
	echo 'Part 1: ', $part1, "\n";

	$part2 = getLastHashIndex($input, 64, 2016);
	echo 'Part 2: ', $part2, "\n";
