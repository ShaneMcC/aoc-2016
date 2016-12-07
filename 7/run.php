#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	function hasABBA($string) {
		debugOut('    Test:', $string, "\n");
		for ($i = 0; $i < strlen($string); $i++) {
			if ($i + 3 >= strlen($string)) { continue; }
			debugOut('        ', sprintf('[%s, %s, %s, %s]', $string{$i}, $string{$i+1}, $string{$i+2}, $string{$i+3}), "\n");
			if ($string{$i} == $string{$i+3} && $string{$i+1} == $string{$i+2} && $string{$i} != $string{$i+1}) {
				debugOut('        ABBA!', "\n");
				return true;
			}
		}
	}

	function supportsTLS($address) {
		$squares = preg_match_all('#(\[.*?\])#', $address, $m);
		$address = preg_replace('#(\[.*?\])#', '####', $address);

		if (hasABBA($address)) {
			foreach ($m[1] as $inbrackets) {
				if (hasABBA($inbrackets)) {
					return false;
				}
			}
			return true;
		}

		return false;

	}

	$part1 = 0;
	foreach ($input as $address) {
		debugOut('supportsTLS: ', $address, "\n");
		$supported = supportsTLS($address);
		debugOut('    => ', ($supported ? 'YES' : 'NO'), "\n");
		if ($supported) { $part1++; }
	}

	echo 'Part 1: ', $part1, "\n";
