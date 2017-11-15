#!/usr/bin/php
<?php
	$__CLI['long'] = ['screen', 'vm'];
	require_once(dirname(__FILE__) . '/../common/common.php');
	require_once(dirname(__FILE__) . '/../common/vm.php');
	require_once(dirname(__FILE__) . '/../8/screen.php');

	$data = VM::parseInstrLines(getInputLines());
	$vm = new VM($data);
	optimiseAll($vm);

	/**
	 * out
	 *   - out x
	 *
	 * Output something.
	 *
	 * @param $vm VM to execute on.
	 * @param $args Args for this instruction.
	 */
	$vm->setInstr('out', function($vm, $args) {
		$bit = chr($vm->isReg($args[0]) ? $vm->getReg($args[0]) : $args[0]);
		$vm->appendOutput($bit);
	});

	if (isDebug() || isset($__CLIOPTS['vm'])) { $vm->setDebug(true, 1); }
	$vm->run();


	$input = explode("\n", trim($vm->getOutput()));
	$screen = new Screen();
	if (isDebug() || isset($__CLIOPTS['screen'])) {
		$screen->drawScreen(false);
		$screen->setDebug(true);
	}

	$screen->parseInput($input);

	$screen->addCharacter(0x00064A4C, 'o');
	$screen->addCharacter(0x1921111E, '2');
	$screen->addCharacter(0x08A52944, '0');
	$screen->addCharacter(0x08C2108E, '1');
	$screen->addCharacter(0x3C221108, '7');

	$output = '';
	$characters = $screen->getScreenCharacters();
	foreach ($characters as $c) { $output .= $screen->decodeCharacter($c); }
	echo 'Output: ', $output, "\n";
