#!/usr/bin/php
<?php
	$__CLI['short'] = ['1', '2'];
	$__CLI['long'] = ['slow'];
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	class Decompressor {
		private $version = 1;
		private $input = '';

		public function __construct($input, $version = 1) {
			$this->input = $input;
			$this->version = $version;
		}

		function showOutput($position) {
			global $__CLIOPTS;

			echo $this->input, "\n";
			echo str_repeat(' ', $position), '^ (', $position, ')', "\n";

			if (isset($__CLIOPTS['slow'])) { usleep(100000); }
		}

		function decompress() {
			$count = 0;

			for ($i = 0; $i < strlen($this->input); $i++) {
				if (isDebug()) { $this->showOutput($i); }

				if ($this->input{$i} == '(') {
					$next = strpos($this->input, ')', $i) + 1;
					if (!preg_match('#^\(([0-9]+)x([0-9]+)\)#', substr($this->input, $i, $next - $i), $m)) { continue; }
					list($all, $chars, $times) = $m;

					$nextText = substr($this->input, $next, $chars);
					$markerRange = $i + ($chars * $times);
					$markerLength = $chars + ($next - $i);

					// Replace the text in our version of the input String.
					$this->input = substr_replace($this->input, str_repeat($nextText, $times), $i, $markerLength);

					// Skip ahead in version 1 to the end of the replaced
					// string.
					if ($this->version == 1) { $i = $markerRange; }

					// In version 2 we need to decode our new bits, so
					// we need to stay where we are and keep going.
					//
					// In version 1, we skipped ahead above to the NEXT
					// character, so skip back one so that the loop advances us
					// to the right place.
					$i--;
				}
			}

			if (isDebug()) { $this->showOutput($i); }

			return $i;
		}
	}

	if (!isset($__CLIOPTS['2'])) {
		$part1 = (new Decompressor($input, 1))->decompress();
		echo 'Part 1: ', $part1, "\n";
	}

	if (isDebug()) { echo "\n\n"; }

	if (!isset($__CLIOPTS['1'])) {
		$part2 = (new Decompressor($input, 2))->decompress();
		echo 'Part 2: ', $part2, "\n";
	}
