<?php

	class Screen {
		private $input = '';
		private $screenChars = [' ', '█'];

		private $screenWidth = 0;
		private $screenHeight = 0;
		private $screen = [];

		private $debug = false;
		private $buggy = false;
		private $sleep = 25000;

		private $encodedCharacters = [];

		public function __construct($width = 50, $height = 6) {
			$this->screenWidth = $width;
			$this->screenHeight = $height;

			$this->reset();
		}

		public function reset() {
			// From: https://www.reddit.com/r/adventofcode/comments/5h52ro/2016_day_8_solutions/daxv8cr/
			// Added missing characters as 0xFF for now to make them stand out.
			$encodedCharacters = [0x19297A52 => 'A', 0x392E4A5C => 'B', 0x1928424C => 'C',
			                      0x39294A5C => 'D', 0x3D0E421E => 'E', 0x3D0E4210 => 'F',
			                      0x19285A4E => 'G', 0x252F4A52 => 'H', 0x1C42108E => 'I',
			                      0x0C210A4C => 'J', 0x254C5292 => 'K', 0x2108421E => 'L',
			                      0xFF       => 'M', 0xFF       => 'N', 0x19294A4C => 'O',
			                      0x39297210 => 'P', 0xFF       => 'Q', 0x39297292 => 'R',
			                      0x1D08305C => 'S', 0x1C421084 => 'T', 0x25294A4C => 'U',
			                      0xFF       => 'V', 0xFF       => 'W', 0xFF       => 'X',
			                      0x23151084 => 'Y', 0x3C22221E => 'Z'];

			// Characters for https://www.reddit.com/r/adventofcode/comments/5h9sfd/2016_day_8_tampering_detected/
			$encodedCharacters += [0x252D5A52 => 'N', 0x3E421084 => 'T', 0x2318A944 => 'V', 0x00000000 => ' '];

			// Characters for https://www.reddit.com/r/adventofcode/comments/5h571u/2016_day_8_generate_an_input/day4ctx/
			$encodedCharacters += [0x239AD671 => 'N'];

			$this->encodedCharacters = $encodedCharacters;

 			$this->screen = array_fill(0, $this->screenHeight, array_fill(0, $this->screenWidth, $this->screenChars[false]));
		}

		public function setDebug($debug) {
			$this->debug = $debug;
		}

		public function setSleep($sleep) {
			$this->sleep = $sleep;
		}

		public function setBuggy($buggy) {
			$this->buggy = $buggy;
		}

		public function drawScreen($redraw = false) {
			// Redraw over previous screen by moving the cursor up.
			if ($redraw) { echo "\033[" . (count($this->screen) + 2) . "A"; }

			echo '┍', str_repeat('━', count($this->screen[0])), '┑', "\n";
			foreach ($this->screen as $row) { echo '│', implode('', $row), '│', "\n"; }
			echo '┕', str_repeat('━', count($this->screen[0])), '┙', "\n";
		}

		public function rotateArray($array, $count) {
			for ($i = 0; $i < $count; $i++) {
				array_unshift($array, array_pop($array));
			}

			return $array;
		}

		public function getScreenCharacters() {
			$characters = array();

			foreach ($this->screen as $row) {
				for ($i = 0; $i < count($this->screen[0]); $i += 5) {
					$c = $i / 5;
					$characters[$c][] = array_slice($row, $i, 5);
				}
			}

			return $characters;
		}

		public function addCharacter($encoded, $character) {
			$this->encodedCharacters += [$encoded => $character];
		}

		public function decodeCharacter($character) {
			$char = (int)bindec(str_replace($this->screenChars, [0, 1], implode('', array_map('implode', $character))));

if (!isset($this->encodedCharacters[$char])) {
	echo 'Unknown Char: ', $char, "\n";
}

			return isset($this->encodedCharacters[$char]) ? $this->encodedCharacters[$char] : '?';
		}

		public function parseInput($input) {
			foreach ($input as $details) {
				preg_match('#^(rect|rotate) (?:(row|column) (?:x|y)=([0-9]+) by ([0-9]+)|([0-9]+)x([0-9]+))#', trim($details), $m);
				$instr = $m[1];

				if ($instr == "rect") {
					list($all, $instr, $_, $_, $_, $rX, $rY) = $m;

					foreach (yieldXY(0, 0, $rX-1, $rY-1) as $col => $row) {
						// Tampering detected! It's ok, I got it...
						if ($this->buggy) { $row ^= $col ^= $row ^= $col; }
						if (isset($this->screen[$row][$col])) { $this->screen[$row][$col] = $this->screenChars[true]; }
					}

				} else if ($instr == "rotate") {
					list($all, $instr, $type, $which, $by) = $m;

					if ($type == "row" && isset($this->screen[$which])) {
						$this->screen[$which] = $this->rotateArray($this->screen[$which], $by);
					} else if ($type == "column" && isset($this->screen[0][$which])) {
						$col = $this->rotateArray(array_column($this->screen, $which), $by);
						// Merge the column back into the array.
						for ($i = 0; $i < count($col); $i++) { $this->screen[$i][$which] = $col[$i]; }
					}
				}

				if ($this->debug) { $this->drawScreen(true); usleep($this->sleep); }
			}

			if ($this->debug) { echo "\n"; }
		}

		public function getScreen() {
			return $this->screen;
		}

		public function getDisplayChars() {
			return $this->screenChars;
		}
	}
