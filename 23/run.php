#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	require_once(dirname(__FILE__) . '/../12/vm.php');

	$data = VM::parseInstrLines(getInputLines());
	$vm = new VM($data);

	/**
	 * tgl
	 *   - tgl x
	 *
	 * Does weird toggling of instructions for no sane reason!
	 *
	 * @param $vm VM to execute on.
	 * @param $args Args for this instruction.
	 */
	$vm->setInstr('tgl', function($vm, $args) {
		debugOut('tgl [', implode(' ', $args), ']', "\n");
		$x = $args[0];
		if ($vm->isReg($x)) { $x = $vm->getReg($x); }

		$loc = $vm->getLocation() + (int)$x;
		if ($vm->hasData($loc)) {
			$data = $vm->getData($loc);

			if ($data[0] == 'inc') { $data[0] = 'dec'; }
			else if ($data[0] == 'dec' || $data[0] == 'tgl') { $data[0] = 'inc'; }
			else if ($data[0] == 'jnz') { $data[0] = 'cpy'; }
			else if ($data[0] == 'cpy') { $data[0] = 'jnz'; }

			$vm->setData($loc, $data);
		}
	});

	// Optimise away the multiply.
	//    cpy b c
	//    inc a
	//    dec c
	//    jnz c -2
	//    dec d
	//    jnz d -5
	//
	//    Sets A = b * d
	//    Clears C and D.
	for ($i = 0; $i < count($data); $i++) {
		if (!isset($data[$i + 5])) { break; }

		if ($data[$i][0] == 'cpy' &&
			$data[$i + 1][0] == 'inc' &&
			$data[$i + 2][0] == 'dec' &&
			$data[$i + 3][0] == 'jnz' && $data[$i + 3][1][1] == '-2' &&
			$data[$i + 4][0] == 'dec' &&
			$data[$i + 5][0] == 'jnz' && $data[$i + 5][1][1] == '-5') {

			$data[$i][0] = '_OPT_MUL';

			$a = $data[$i + 1][1][0];
			$b = $data[$i][1][0];
			$c = $data[$i + 3][1][0];
			$d = $data[$i + 5][1][0];

			$data[$i][1] = [$a, $b, $c, $d];

			$data[$i + 1] = ['_OPT_NOOP', []];
			$data[$i + 2] = ['_OPT_NOOP', []];
			$data[$i + 3] = ['_OPT_NOOP', []];
			$data[$i + 4] = ['_OPT_NOOP', []];
			$data[$i + 5] = ['_OPT_NOOP', []];
		}
	}

	$vm->setInstr('_OPT_MUL', function($vm, $args) {
		debugOut('_OPT_MUL [', implode(' ', $args), ']', "\n");
		list($a, $b, $c, $d) = $args;

		if ($vm->isReg($b)) { $b = $vm->getReg($b); }
		if ($vm->isReg($c)) { $c2 = $c; $c = $vm->getReg($c); $vm->setReg($c2, 0); }
		if ($vm->isReg($d)) { $d2 = $d; $d = $vm->getReg($d); $vm->setReg($d2, 0); }

		$vm->setReg($a, $b * $d);
	});
	$vm->setInstr('_OPT_NOOP', function($vm, $args) { debugOut('_OPT_NOOP [', implode(' ', $args), ']', "\n"); });


	$vm->loadProgram($data);
	$vm->setReg('a', 7);
	$vm->run();
	echo 'Part 1: ', $vm->dumpReg(), "\n";

	debugOut("\n\n");

	$vm->loadProgram($data);
	$vm->setReg('a', 12);
	$vm->run();
	echo 'Part 2: ', $vm->dumpReg(), "\n";
