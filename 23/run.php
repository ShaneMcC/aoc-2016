#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$data = array();
	foreach ($input as $lines) {
		if (preg_match('#([a-z]{3}) ([^\s]+)(?: (.*))?#SADi', $lines, $m)) {
			$data[] = array($m[1], array_slice($m, 2));
		}
	}

	/**
	 * Simple 4-register, 5-instruction VM for Day 23, based on day 12.
	 */
	class VM {
		/** Current location. */
		private $location = -1;

		/** Known Instructions. */
		private $instrs = array();

		/** Internal Registers. */
		private $registers = array('a' => 0, 'b' => 0, 'c' => 0, 'd' => 0);

		/** Data to execute. */
		private $data = array();

		/**
		 * Create a new VM.
		 *
		 * @param $data (Optional) Program execution data.
		 */
		function __construct($data = array()) {
			$this->init();
			$this->loadProgram($data);
		}

		/**
		 * Load in a new program and reset the VM State.
		 *
		 * @param $data Data to load.
		 */
		function loadProgram($data) {
			$this->data = $data;
			$this->location = -1;
			$this->registers = array('a' => 0, 'b' => 0, 'c' => 0, 'd' => 0);
		}

		/**
		 * Get the instruction function by the given name.
		 *
		 * @param $instr Instruction name.
		 * @return Instruction function.
		 */
		private function getInstr($instr) {
			if (isset($this->instrs[$instr])) { return $this->instrs[$instr]; }
			throw new Exception('Unknown Instr: ' . $instr);
		}

		/**
		 * Get the data at the given location.
		 *
		 * @param $location Data location.
		 * @return Data from location.
		 */
		private function hasData($loc) {
			return isset($this->data[$loc]);
		}

		/**
		 * Get the data at the given location.
		 *
		 * @param $location Data location.
		 * @return Data from location.
		 */
		private function getData($loc) {
			if (isset($this->data[$loc])) { return $this->data[$loc]; }
			throw new Exception('Unknown Data Location: ' . $loc);
		}

		/**
		 * Set the data at the given location.
		 *
		 * @param $location Data location.
		 * @param $val New Value
		 */
		private function setData($loc, $val) {
			if (isset($this->data[$loc])) {
				$this->data[$loc] = $val;
			} else {
				throw new Exception('Unknown Data Location: ' . $loc);
			}

		}

		/**
		 * Iniit the Instructions.
		 */
		private function init() {
			/**
			 * cpy
			 *   - cpy x y
			 *
			 * Copies x (either an integer or the value of a register) into register y.
			 *
			 * @param $vm VM to execute on.
			 * @param $args Args for this instruction.
			 */
			$this->instrs['cpy'] = function($vm, $args) {
				debugOut('cpy [', implode(' ', $args), ']', "\n");
				$x = $args[0];
				$y = $args[1];
				if ($vm->isReg($x)) { $x = $vm->getReg($x); }
				if (!$vm->isReg($y)) { return; }
				$vm->setReg($y, $x);
			};

			/**
			 * inc
			 *   - inc x
			 *
			 * Increases the value of register x by one.
			 *
			 * @param $vm VM to execute on.
			 * @param $args Args for this instruction.
			 */
			$this->instrs['inc'] = function($vm, $args) {
				debugOut('inc [', implode(' ', $args), ']', "\n");
				$reg = $args[0];
				if (!$vm->isReg($reg)) { return; }
				$val = $vm->getReg($reg) + 1;
				$vm->setReg($reg, $val);
			};

			/**
			 * dec
			 *   - dec x
			 *
			 * decreases the value of register x by one.
			 *
			 * @param $vm VM to execute on.
			 * @param $args Args for this instruction.
			 */
			$this->instrs['dec'] = function($vm, $args) {
				debugOut('dec [', implode(' ', $args), ']', "\n");
				$reg = $args[0];
				if (!$vm->isReg($reg)) { return; }
				$val = $vm->getReg($reg) - 1;
				$vm->setReg($reg, $val);
			};

			/**
			 * tgl
			 *   - tgl x
			 *
			 * Does weird toggling of instructions for no sane reason!
			 *
			 * @param $vm VM to execute on.
			 * @param $args Args for this instruction.
			 */
			$this->instrs['tgl'] = function($vm, $args) {
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
			};

			/**
			 * jnz
			 *   - jnz x y
			 *
			 * jumps to an instruction y away (positive means forward;
			 * negative means backward), but only if x is not zero.
			 *
			 * @param $vm VM to execute on.
			 * @param $args Args for this instruction.
			 */
			$this->instrs['jnz'] = function($vm, $args) {
				debugOut('jnz [', implode(' ', $args), ']', "\n");

				$x = $args[0];
				$y = $args[1];

				if ($vm->isReg($x)) { $x = $vm->getReg($x); }
				if ($vm->isReg($y)) { $y = $vm->getReg($y); }
				if ($x === 0) { return; }

				$newloc = $vm->getLocation() + (int)$y;
				$this->jump($newloc - 1); // (-1 because step() always does +1)
			};
		}

		/**
		 * Get the current execution location.
		 *
		 * @return Location of current execution.
		 */
		function getLocation() {
			return $this->location;
		}

		/**
		 * Jump to specific location.
		 *
		 * @param $loc Location to jump to.
		 */
		function jump($loc) {
			$this->location = $loc;
		}

		/**
		 * Step a single instruction.
		 *
		 * @return True if we executed something, else false if we have no more
		 *         to execute.
		 */
		function step() {
			if (isset($this->data[$this->location + 1])) {
				$this->location++;
				$next = $this->data[$this->location];

				$instr = $next[0];
				$data = $next[1];

				$ins = $this->getInstr($instr);
				$ins($this, $data);

				return TRUE;
			} else {
				return FALSE;
			}
		}

		/**
		 * Continue stepping through untill we reach the end.
		 */
		function run() {
			while ($this->step()) { }
		}

		/**
		 * Check if the given input is a valid register.
		 *
		 * @param $reg Register to check
		 * @return True if valid register.
		 */
		function isReg($reg) {
			return isset($this->registers[$reg]);
		}

		/**
		 * Get the value of the given register.
		 *
		 * @param $reg Register to get value of
		 * @return Value of $reg
		 */
		function getReg($reg) {
			if (isset($this->registers[$reg])) { return $this->registers[$reg]; }
			throw new Exception('Unknown Register: ' . $reg);
		}

		/**
		 * Set the value of the given register.
		 *
		 * @param $reg Register to Set value of
		 * @param $val Value to set register to.
		 */
		function setReg($reg, $val) {
			if (isset($this->registers[$reg])) { $this->registers[$reg] = $val; return $val; }
			throw new Exception('Unknown Register: ' . $reg);
		}

		/**
		 * Set the value of the given register.
		 *
		 * @param $reg Register to Set value of
		 * @param $val Value to set register to.
		 */
		function dumpReg() {
			$out = [];
			foreach ($this->registers as $reg => $val) {
				$out[] = $reg . ': ' . $val;
			}
			return '[' . implode('] [', $out) . ']';
		}
	}

	$vm = new VM($data);
	$vm->setReg('a', 7);
	$vm->run();
	echo 'Part 1: ', $vm->dumpReg(), "\n";

	debugOut("\n\n");

	$vm->loadProgram($data);
	$vm->setReg('a', 12);
	$vm->run();
	echo 'Part 2: ', $vm->dumpReg(), "\n";
