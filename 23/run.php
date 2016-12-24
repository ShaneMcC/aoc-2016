#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	require_once(dirname(__FILE__) . '/../12/vm.php');

	$data = VM::parseInstrLines(getInputLines());
	$vm = new VM($data);
	optimiseAll($vm);

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

	$vm->setReg('a', 7);
	$vm->run();
	echo 'Part 1: ', $vm->dumpReg(), "\n";

	debugOut("\n\n");

	$vm->loadProgram($data);
	$vm->setReg('a', 12);
	$vm->run();
	echo 'Part 2: ', $vm->dumpReg(), "\n";
