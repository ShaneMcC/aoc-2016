#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	require_once(dirname(__FILE__) . '/vm.php');
	$data = VM::parseInstrLines(getInputLines());
	$vm = new VM($data);


	// Optimise a bit.
	//    inc a
	//    dec b
	//    jnz b -2
	//
	//    A += B;
	//    B = 0
	for ($i = 0; $i < count($data); $i++) {
		if (!isset($data[$i + 2])) { break; }

		if ($data[$i][0] == 'inc' &&
			$data[$i + 1][0] == 'dec' &&
			$data[$i + 2][0] == 'jnz' && $data[$i + 2][1][1] == '-2') {

			$data[$i][0] = '_OPT_ADD_TO';

			$a = $data[$i][1][0];
			$b = $data[$i + 1][1][0];

			$data[$i][1] = [$a, $b];

			$data[$i + 1] = ['_OPT_NOOP', []];
			$data[$i + 2] = ['_OPT_NOOP', []];
		}
	}

	$vm->setInstr('_OPT_ADD_TO', function($vm, $args) {
		debugOut('_OPT_ADD_TO [', implode(' ', $args), ']', "\n");
		list($a, $b) = $args;

		if ($vm->isReg($b)) { $b2 = $b; $b = $vm->getReg($b); $vm->setReg($b2, 0); }

		$vm->setReg($a, $vm->getReg($a) + $b);
	});
	$vm->setInstr('_OPT_NOOP', function($vm, $args) { debugOut('_OPT_NOOP [', implode(' ', $args), ']', "\n"); });


	$vm->loadProgram($data);
	$vm->run();
	echo 'Part 1: ', $vm->dumpReg(), "\n";

	debugOut("\n\n");

	$vm->loadProgram($data);
	$vm->setReg('c', 1);
	$vm->run();
	echo 'Part 2: ', $vm->dumpReg(), "\n";
