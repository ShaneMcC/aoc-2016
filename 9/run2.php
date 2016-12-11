#!/usr/bin/php
<?php
	$__CLI['short'] = ['1', '2'];
	$__CLI['long'] = ['slow', 'complete', 'limit:'];
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	/**
	 * Easter-Bunny Decompressor.
	 */
	class Decompressor {
		/** What version of the compression format are we looking at? */
		private $version = 1;
		/** What was the original input to this Decompressor? */
		private $input = '';

		/** Do we only care about the output size rather than the actual output? */
		private $sizeOnly = false;

		/** Decompression input. */
		private $output = '';
		/** Have we already decompressed original into input? */
		private $decompressed = false;
		/** If the output has been truncated, how many characters are missing? */
		private $base = 0;

		/** What position to truncate at? */
		private $truncateAt = 1000000;

		/**
		 * Create a new Decompressor
		 *
		 * @param $input String to decompress.
		 * @param $version (Default: 1) Version of compression format.
		 */
		public function __construct($input, $version = 1, $sizeOnly = false) {
			$this->input = $input;
			$this->version = $version;
			$this->sizeOnly = $sizeOnly;
			$this->reset();
		}

		/**
		 * Get the input used in this decompressor.
		 *
		 * @return Input to decompress
		 */
		function getInput() {
			return $this->input;
		}

		/**
		 * Get the version of this decompressor.
		 *
		 * @return Version of decompressor
		 */
		function getVersion() {
			return $this->version;
		}

		/**
		 * Does this decompressor only care about size?
		 *
		 * @return true if output may be truncated.
		 */
		function isSizeOnly() {
			return $this->sizeOnly;
		}

		/**
		 * Reset the decompressor ready to decompress.
		 */
		public function reset() {
			$this->output = $this->input;
			$this->base = 0;
			$this->decompressed = false;
			$this->decompressedSize = -1;
		}

		/**
		 * Show debugging output to the console.
		 *
		 * @param $position Position we are currently at.
		 */
		function showOutput($position) {
			global $__CLIOPTS;

			$displayTruncate = 100;

			$displayStart = max(0, $position - $displayTruncate/2);
			$displayPos = $position;
			$displayEnd = min(strlen($this->output), $displayStart + $displayTruncate);

			if ($this->base > 0 || $displayStart > 0) { echo ' ...'; }

			echo substr($this->output, $displayStart, ($displayEnd - $displayStart));

			if (strlen($this->output) > $displayEnd) { echo '...'; }
			echo "\n";

			if ($this->base > 0 || $displayStart > 0) { echo '    '; }
			echo str_repeat(' ', $displayPos - $displayStart), '^ (', ($position + $this->base), ' / ', (strlen($this->output) + $this->base), ')', "\n"; /* */

			if (isset($__CLIOPTS['slow'])) { usleep(100000); }
		}

		/**
		 * Decompress the given input.
		 *
		 * @return Size of input after decompression.
		 */
		function decompress() {
			global $__CLIOPTS;
			if ($this->decompressed) { return $this->getSize(); }

			$count = 0;

			for ($i = 0; $i < strlen($this->output); $i++) {
				if (isDebug()) { $this->showOutput($i); }

				if ($i >= $this->truncateAt) {
					if (!isDebug()) { $this->showOutput($i); }
					$this->truncate($i);
					$i = 0;
				}

				if (isset($__CLIOPTS['limit']) && strlen($this->output) > $__CLIOPTS['limit']) {
					throw new DecompressorException('Output breached size limit. (' . strlen($this->output) . ' > ' . $__CLIOPTS['limit'] . ')');
				}

				if ($this->output{$i} == '(') {
					$next = strpos($this->output, ')', $i) + 1;
					if (!preg_match('#^\(([0-9]+)x([0-9]+)\)#', substr($this->output, $i, $next - $i), $m)) { continue; }
					list($all, $chars, $times) = $m;

					$nextText = substr($this->output, $next, $chars);
					if ($next + $chars > strlen($this->output)) {
						throw new DecompressorException('Invalid Marker Range.');
					}

					$markerRange = $i + ($chars * $times);
					$markerLength = $chars + ($next - $i);

					// Replace the text in our version of the input String.
					$this->output = substr_replace($this->output, str_repeat($nextText, $times), $i, $markerLength);

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
				} else if (!isset($__CLIOPTS['complete'])) {
					// Skip ahead to the next '(';
					$next = strpos($this->output, '(', $i);
					if ($next === false) { $next = strlen($this->output); }
					$i = $next - 1;
				}
			}

			if (isDebug()) { $this->showOutput($i); }

			$this->decompressed = true;

			return $i + $this->base;
		}

		/**
		 * If we only want to know the size, not the output, then we can
		 * truncate earlier parts of the file as we pass them, do this here.
		 *
		 * @param $amount How many characters to truncate?
		 */
		function truncate($amount) {
			if (!$this->isSizeOnly()) { return; }
			$this->output = substr($this->output, $amount);
			$this->base += $amount;
		}

		/**
		 * Get Output
		 *
		 * @return Output from decompression (false if not decompressed yet.)
		 */
		function getOutput() {
			return $this->decompressed ? $this->output : FALSE;
		}

		/**
		 * Get Size of decompressed output
		 *
		 * @return size of output from decompression (false if not decompressed yet.)
		 */
		function getSize() {
			return $this->decompressed ? strlen($this->output) + $this->base : FALSE;
		}
	}
	class DecompressorException extends Exception { }


	if (!isset($__CLIOPTS['2'])) {
		$part1 = new Decompressor($input, 1, true);
		try {
			$part1->decompress();
			echo 'Part 1: ', $part1->getSize(), "\n";
		} catch (DecompressorException $d) {
			echo 'Failed to decompress part 1: ', $d->getMessage(), "\n";
		}
	}

	if (isDebug()) { echo "\n\n"; }

	if (!isset($__CLIOPTS['1'])) {
		try {
			$part2 = new Decompressor($input, 2, false);
			$part2->decompress();
			echo 'Part 2: ', $part2->getSize(), "\n";
		} catch (DecompressorException $d) {
			echo 'Failed to decompress part 2: ', $d->getMessage(), "\n";
		}
	}
