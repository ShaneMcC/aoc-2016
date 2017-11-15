<?php

	/**
	 * Add optimisation for += operations.
	 *    0: inc a
	 *    1: dec b
	 *    2: jnz b -2
	 *
     * OR
	 *    0: dec b
	 *    1: inc a
	 *    2: jnz b -2
	 *
	 *    A += B;
	 *    B = 0
	 */
	function optimiseAdd($vm) {
		$vm->addReadAhead(function ($vm) {
			$loc = $vm->getLocation();
			if (!$vm->hasData($loc + 2)) { return FALSE; }

			$data = [];
			for ($i = 0; $i <= 2; $i++) { $data[$i] = $vm->getData($loc + $i); }

			$type = 0;

			// Check for matching instructions.
			if ($data[0][0] == 'inc' &&
				$data[1][0] == 'dec' &&
				$data[2][0] == 'jnz' && $data[2][1][0] == $data[1][1][0] && $data[2][1][1] == '-2') {
				$type = 1;
			} else if ($data[0][0] == 'dec' &&
				$data[1][0] == 'inc' &&
				$data[2][0] == 'jnz' && $data[2][1][0] == $data[0][1][0] && $data[2][1][1] == '-2') {

				$type = 2;
			}

			if ($type > 0) {
				debugOut('Optimised Add: ');
				debugOut(VM::instrToString($data[0]), ' -> ');
				debugOut(VM::instrToString($data[1]), ' -> ');
				debugOut(VM::instrToString($data[2]), "\n");

				if ($type == 1) {
					list($a, $b) = [$data[0][1][0], $data[1][1][0]];
				} else if ($type == 2) {
					list($b, $a) = [$data[0][1][0], $data[1][1][0]];
				}

				$bVal = $vm->isReg($b) ? $vm->getReg($b) : $b;

				$vm->setReg($a, $vm->getReg($a) + $bVal);
				$vm->setReg($b, 0);

				// Jump to after the add
				return $loc + 3;
			}

			return FALSE;
		});
	}

	/**
	 * Add optimisation for multiply operations.
	 *   0: cpy b c
	 *   1: inc a
	 *   2: dec c
	 *   3: jnz c -2
	 *   4: dec d
	 *   5: jnz d -5
	 *
	 *    Sets A += b * d
	 *    (B may or may not be a register, A, C and D are.)
	 *    Clears C and D.
	 */
	function optimiseMultiply($vm) {
		$vm->addReadAhead(function ($vm) {
			$loc = $vm->getLocation();
			if (!$vm->hasData($loc + 5)) { return FALSE; }

			$data = [];
			for ($i = 0; $i <= 5; $i++) { $data[$i] = $vm->getData($loc + $i); }

			// Check for matching instructions.
			if ($data[0][0] == 'cpy' &&
				$data[1][0] == 'inc' &&
				$data[2][0] == 'dec' && $data[2][1][0] == $data[0][1][1] &&
				$data[3][0] == 'jnz' && $data[3][1][0] == $data[2][1][0] && $data[3][1][1] == '-2' &&
				$data[4][0] == 'dec' &&
				$data[5][0] == 'jnz' && $data[5][1][0] == $data[4][1][0] && $data[5][1][1] == '-5') {

				list($a, $b, $c, $d) = [$data[1][1][0], $data[0][1][0], $data[3][1][0], $data[5][1][0]];
				if (!$vm->isReg($a) || !$vm->isReg($c) || !$vm->isReg($d)) { return FALSE; }

				debugOut('Optimised Multiply: ');
				debugOut(VM::instrToString($data[0]), ' -> ');
				debugOut(VM::instrToString($data[1]), ' -> ');
				debugOut(VM::instrToString($data[2]), ' -> ');
				debugOut(VM::instrToString($data[3]), ' -> ');
				debugOut(VM::instrToString($data[4]), ' -> ');
				debugOut(VM::instrToString($data[5]), "\n");

				$bVal = $vm->isReg($b) ? $vm->getReg($b) : $b;
				$dVal = $vm->getReg($d);

				$vm->setReg($c, 0);
				$vm->setReg($d, 0);

				$vm->setReg($a, $vm->getReg($a) + ($bVal * $dVal));

				// Jump to after the multiply
				return $loc + 6;
			}

			return FALSE;
		});
	}

	/**
	 * Enable all optimisations.
	 *
	 * @param $vm VM to optimise.
	 */
	function optimiseAll($vm) {
		optimiseAdd($vm);
		optimiseMultiply($vm);
	}
