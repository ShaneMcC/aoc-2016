#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	function hasABBA($address) {
		return preg_match('#(.)((?:(?!\1).))\2\1#', preg_replace('#(\[.*?\])#', '####', $address));
	}

	function getABAs($address) {
		preg_match_all('#(?=((.)((?:(?!\2).))\2))#', preg_replace('#(\[.*?\])#', '###', $address), $abas);
		$babs = preg_replace('#(.)(.).#', '\2\1\2', $abas[1]);
		return array_combine($abas[1], $babs);
	}

	function supportsTLS($address) {
		preg_match_all('#\[(.*?)\]#', $address, $hypernets);

		if (hasABBA($address)) {
			foreach ($hypernets[1] as $hypernet) {
				if (hasABBA($hypernet)) {
					return false;
				}
			}
			return true;
		}

		return false;
	}

	function supportsSSL($address) {
		preg_match_all('#\[(.*?)\]#', $address, $hypernets);
		$abas = getABAs($address);

		foreach ($abas as $aba => $bab) {
			foreach ($hypernets[1] as $hypernet) {
				if (strpos($hypernet, $bab) !== false) {
					return true;
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
