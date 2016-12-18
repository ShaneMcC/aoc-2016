#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	function dragonCurve($input) {
		return $input . '0' . str_replace('#', '1', str_replace(['0', '1'], ['#', '0'], strrev($input)));;
	}

	function getChecksum($data, $length) {
		$data = substr($data, 0, $length);

		$checksum = '';
		if (preg_match_all('#(..)#', $data, $m)) {
			foreach ($m[0] as $pair) {
				$checksum .= ($pair == '11' || $pair == '00') ? '1' : '0';
			}
		}
		while (strlen($checksum) > 2 && strlen($checksum) % 2 == 0) { $checksum = getChecksum($checksum, $length); }
		return $checksum;
	}

	function fillDisk($data, $size) {
		while (strlen($data) < $size) { $data = dragonCurve($data); }
		return getChecksum($data, $size);
	}

	if (isTest()) {
		echo 'Part 1: ', fillDisk('10000', 20), "\n";
	} else {
		echo 'Part 1: ', fillDisk($input, 272), "\n";
	}
