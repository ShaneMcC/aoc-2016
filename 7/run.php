#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	function hasABBA($string) {
		return preg_match('#(.)((?:(?!\1).))\2\1#', $string);
	}

	function getABAs($string) {
		preg_match_all('#(?=((.)((?:(?!\2).))\2))#', $string, $abas);
		$babs = preg_replace('#(.)(.).#', '\2\1\2', $abas[1]);
		return array_combine($abas[1], $babs);
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
			foreach ($abas as $aba => $bab) {
				foreach ($m[1] as $inbrackets) {
					if (strpos($inbrackets, $bab) !== false) {
						return true;
					}
				}
			}
		}

		return false;
	}

	$part1 = $part2 = 0;
	foreach ($input as $address) {
		if (supportsTLS($address)) { $part1++; }
		if (supportsSSL($address)) { $part2++; }
	}

	echo 'Part 1: ', $part1, "\n";
	echo 'Part 2: ', $part2, "\n";
