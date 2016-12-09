#!/usr/bin/php
<?php
	$__CLI['long'] = ['repeat-first'];
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	function decompress($line, $version = 1) {
		global $__CLIOPTS;
		$count = 0;
		for ($i = 0; $i < strlen($line); $i++) {
			if ($line{$i} == '(') {
				$cur = $i;
				$i = strpos($line, ')', $i);

				preg_match('#([0-9]+)x([0-9]+)#', substr($line, $cur, $i - $cur), $m);
				list($all, $chars, $times) = $m;

				$next = substr($line, $i + 1, $chars);
				if (isset($__CLIOPTS['repeat-first'])) {
					$next = str_repeat($next, $times);
					$count += ($version == 1) ? strlen($next) : decompress($next, $version);
				} else {
					$count += (($version == 1) ? strlen($next) : decompress($next, $version)) * $times;
				}
				$i += $chars;
			} else {
				$count++;
			}
		}

		return $count;
	}

	echo 'Part 1: ', decompress($input, 1), "\n";
	echo 'Part 2: ', decompress($input, 2), "\n";
