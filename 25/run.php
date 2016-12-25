#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	require_once(dirname(__FILE__) . '/../12/vm.php');

	class TransmitterVM extends VM {
		private $output = '';
		public function clearOutput() { $this->output = ''; }
		public function getOutput() { return $this->output; }
		public function getOutputLength() { return strlen($this->output); }
		public function appendOutput($str) { $this->output .= $str; }
		public function reset() { $this->clearOutput(); parent::reset(); }
	}

	$data = VM::parseInstrLines(getInputLines());
	$vm = new TransmitterVM($data);
	optimiseAll($vm);

	/**
	 * out
	 *   - out x
	 *
	 * Transmit outwards!
	 *
	 * @param $vm VM to execute on.
	 * @param $args Args for this instruction.
	 */
	$vm->setInstr('out', function($vm, $args) {
		$x = $args[0];
		if ($vm->isReg($x)) { $x = $vm->getReg($x); }

		$vm->appendOutput($x);

		$lastChars = substr($vm->getOutput(), -2);
		if (strlen($lastChars) == 2 && ($lastChars == '11' || $lastChars == '00')) {
			$vm->end(1); // Bad output, exit.
		} else if ($vm->getOutputLength() >= 12) {
			$vm->end(0); // We've probably got right code, exit.
		}
	});

	$i = 0;
	while (true) {
		$vm->reset();
		$vm->setReg('a', $i);
		$vm->run();
		echo $i, ': ', $vm->getOutput(), "\r";
		if ($vm->exitCode() == 0) { break; } else { $i++; }
	}

	echo 'Part 1: ', $i, "\n";
