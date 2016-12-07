#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	function hasABBA($string) {
		debugOut('    hasABBA:', $string, "\n");
		for ($i = 0; $i < strlen($string); $i++) {
			if ($i + 3 >= strlen($string)) { continue; }
			debugOut('        ', sprintf('[%s, %s, %s, %s]', $string{$i}, $string{$i+1}, $string{$i+2}, $string{$i+3}), "\n");
			if ($string{$i} == $string{$i+3} && $string{$i+1} == $string{$i+2} && $string{$i} != $string{$i+1}) {
				debugOut('        ABBA!', "\n");
				return true;
			}
		}
	}

	function getABAs($string) {
		$abas = [];
		debugOut('    getABAs:', $string, "\n");
		for ($i = 0; $i < strlen($string); $i++) {
			if ($i + 2 >= strlen($string)) { continue; }
			debugOut('        ', sprintf('[%s, %s, %s]', $string{$i}, $string{$i+1}, $string{$i+2}), "\n");
			if ($string{$i} == $string{$i+2} && $string{$i} != $string{$i+1}) {
				debugOut('        ABA!', "\n");
				$abas[] = [$string{$i}.$string{$i+1}.$string{$i+2}, $string{$i+1}.$string{$i}.$string{$i+1}];
			}
		}

		return $abas;
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

	function supportsSSL($address) {
		$squares = preg_match_all('#(\[.*?\])#', $address, $m);
		$address = preg_replace('#(\[.*?\])#', '###', $address);
		$abas = getABAs($address);

		if (count($abas) > 0) {
			foreach ($abas as $aba) {
				foreach ($m[1] as $inbrackets) {
					if (strpos($inbrackets, $aba[1]) !== false) {
						return true;
					}
				}
			}
		}

		return false;
	}

	$part1 = $part2 = 0;
	foreach ($input as $address) {
		debugOut('Address: ', $address, "\n");
		$tls = supportsTLS($address);
		$ssl = supportsSSL($address);
		debugOut('    => TLS ', ($tls ? 'YES' : 'NO'), "\n");
		debugOut('    => SSL ', ($ssl ? 'YES' : 'NO'), "\n");
		if ($tls) { $part1++; }
		if ($ssl) { $part2++; }
	}

	echo 'Part 1: ', $part1, "\n";
	echo 'Part 2: ', $part2, "\n";
