#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	require_once(dirname(__FILE__) . '/../12/vm.php');

	$data = VM::parseInstrLines(getInputLines());
	$vm = new VM($data);
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
		global $STRING;
		$x = $args[0];
		if ($vm->isReg($x)) { $x = $vm->getReg($x); }

		$STRING .= $x;
		if (strlen($STRING) >= 10) { throw new Exception('outed a bit'); }
	});

	$vm->addReadAhead(function ($vm) {
		$loc = $vm->getLocation();
		$data = $vm->getData();
		if ($data[0] == 'jnz' && $data[1][0] == '0' && $data[1][1] == '0') { return $loc + 1; }
		return FALSE;
	});

	for ($i = 0; $i < 1000; $i++) {
		try {
			$vm->loadProgram($data);
			$vm->setReg('a', $i);
			$STRING = '';
			$vm->run();
		} catch (Exception $e) { }
		echo $i, ': ', $STRING, "\n";

		if ($STRING == '0101010101') { die(); }
	}
