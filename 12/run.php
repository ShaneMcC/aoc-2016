#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	require_once(dirname(__FILE__) . '/../common/vm.php');
	$data = VM::parseInstrLines(getInputLines());
	$vm = new VM($data);
	optimiseAll($vm);

	$vm->run();
	echo 'Part 1: ', $vm->dumpReg(), "\n";

	debugOut("\n\n");

	$vm->loadProgram($data);
	$vm->setReg('c', 1);
	$vm->run();
	echo 'Part 2: ', $vm->dumpReg(), "\n";
