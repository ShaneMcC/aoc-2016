#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	function getHash($str) {
		global $__HASHES;
		if (!isset($__HASHES[$str])) { $__HASHES[$str] = md5($str); }
		return $__HASHES[$str];
	}

	function validHash($input, $position) {
		$hash = getHash($input . $position);
		if (preg_match('#(.)\1\1#', $hash, $m)) {
			$needle = str_repeat($m[1], 5);
			for ($i = $position + 1; $i < $position + 1000; $i++) {
				if (strpos(getHash($input . $i), $needle) !== FALSE) {
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	$validHashes = array();
	$i = 0;
	while (true) {
		if (validHash($input, $i)) {
			$hash = getHash($input . $i);
			$validHashes[] = $hash;
			debugOut('Found valid hash ', count($validHashes), ' at ', $i, ': ', $hash, "\n");
		}
		if (count($validHashes) == 64) { break; }
		$i++;
	}

	echo 'Part 1: ', $i, "\n";
