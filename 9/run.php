#!/usr/bin/php
<?php
	$__CLI['long'] = ['repeat-first', 'overlap'];
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	function decompress($line, $version = 1, $more = '') {
		global $__CLIOPTS;

		$nextMore = $more;
		$count = 0;
		for ($i = 0; $i < strlen($line); $i++) {
			if ($line{$i} == '(') {
				$cur = $i;
				$i = strpos($line, ')', $i);

				if (!preg_match('#([0-9]+)x([0-9]+)#', substr($line, $cur, $i - $cur), $m)) { die(); }
				list($all, $chars, $times) = $m;

				if (isset($__CLIOPTS['overlap'])) {
					$next = substr($line . $more, $i + 1, $chars);
					$nextMore = substr($line . $more, $i + 1 + $chars);
					// If we ended up borrowing some bits from the rest of the line
					// subtract them from the count, else they'll get double counted.
					$borrowed = strlen($line) - ($i + 1 + $chars);
					if ($borrowed < 0) { $count += $borrowed; }
				} else {
					$next = substr($line, $i + 1, $chars);
				}

				if (isset($__CLIOPTS['repeat-first'])) {
					$next = str_repeat($next, $times);
					$count += ($version == 1) ? strlen($next) : decompress($next, $version, $nextMore);
				} else {
					$count += (($version == 1) ? strlen($next) : decompress($next, $version, $nextMore)) * $times;
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
