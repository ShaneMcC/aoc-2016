<?php
	require_once(dirname(__FILE__) . '/VMOptimisations.php');

	/**
	 * Simple 4-register, 4-instruction VM for Day 12.
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

		/** Read ahead optimisations */
		private $readAheads = array();

		/** Our exit code. */
		private $exitCode = 0;

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
			$this->reset();
		}

		/**
		 * Reset the VM.
		 *
		 * This resets the registers to all-0 and moves the location to the
		 * beginning.
		 */
		function reset() {
			$this->exitCode = 0;
			$this->location = -1;
			$this->registers = array('a' => 0, 'b' => 0, 'c' => 0, 'd' => 0);
		}

		/**
		 * End program execution.
		 *
		 * This sets the location to beyond the data range, effectively
		 * stopping execution.
		 *
		 * @param $exitCode Set the exit code.
		 */
		function end($exitCode = 0) {
			$this->location = count($this->data);
			$this->exitCode = $exitCode;
		}

		/**
		 * Get the vm exit code.
		 *
		 * @return The program exit code.
		 */
		function exitCode() {
			return $this->exitCode;
		}

		/**
		 * Get the instruction function by the given name.
		 *
		 * @param $instr Instruction name.
		 * @return Instruction function.
		 */
		public function getInstr($instr) {
			if (isset($this->instrs[$instr])) { return $this->instrs[$instr]; }
			throw new Exception('Unknown Instr: ' . $instr);
		}

		/**
		 * Set the instruction by the given name to the given function.
		 *
		 * @param $instr Instruction name.
		 * @param $function New function.
		 * @return Instruction function.
		 */
		public function setInstr($instr, $function) {
			$this->instrs[$instr] = $function;
		}

		/**
		 * Get the data at the given location.
		 *
		 * @param $location Data location.
		 * @return Data from location.
		 */
		public function hasData($loc) {
			return isset($this->data[$loc]);
		}

		/**
		 * Get the data at the given location.
		 *
		 * @param $location Data location (or NULL for current).
		 * @return Data from location.
		 */
		public function getData($loc = null) {
			if ($loc == null) { $loc = $this->getLocation(); }
			if (isset($this->data[$loc])) { return $this->data[$loc]; }
			throw new Exception('Unknown Data Location: ' . $loc);
		}

		/**
		 * Set the data at the given location.
		 *
		 * @param $location Data location (or NULL for current).
		 * @param $val New Value
		 */
		public function setData($loc, $val) {
			if ($loc == null) { $loc = $this->getLocation(); }
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
				$reg = $args[0];
				if (!$vm->isReg($reg)) { return; }
				$val = $vm->getReg($reg) - 1;
				$vm->setReg($reg, $val);
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
				$x = $args[0];
				$y = $args[1];

				if ($vm->isReg($x)) { $x = $vm->getReg($x); }
				if ($vm->isReg($y)) { $y = $vm->getReg($y); }
				if ((integer)$x === 0) { return; }

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

				$optimise = $this->doReadAheads();
				// If we optimised, assume we did something, and then we'll
				// continue in he next step
				if ($optimise !== false) {
					// -1 because step() does ++
					$this->location = $optimise - 1;
					return TRUE;
				}

				$next = $this->data[$this->location];
				debugOut(sprintf('(%4s) ', $this->location), VM::instrToString($next), "\n");
				if (isDebug()) { usleep(25000); }
				list($instr, $data) = $next;
				$ins = $this->getInstr($instr);
				$ins($this, $data);

				return TRUE;
			} else {
				return FALSE;
			}
		}

		/**
		 * Read ahead in the script to optimise where possible.
		 * This is called AFTER the location pointer has been moved, but before
		 * the instruction is read.
		 *
		 * Optimisations can either edit the instructions and allow continued
		 * execution (return $vm->getLocation()) or can perform the required
		 * state manipulation themselves and provide a new location to continue
		 * from.
		 *
		 * We stop processing optimisations after the first non-FALSE return.
		 *
		 * @return FALSE if no optimisations were made, else a location index
		 *         for the next instruction we should run.
		 */
		function doReadAheads() {
			foreach ($this->readAheads as $function) {
				$return = call_user_func($function, $this);
				if ($return !== FALSE && $return !== NULL) { return $return; }
			}
			return FALSE;
		}

		/**
		 * Add a new ReadAhead optimiser for doReadAheads to use.
		 *
		 * @param $function Function to call in doReadAheads, should accept 1
		 *        parameter (which will be $this)  and return FALSE if no
		 *        optimisation occured, or a new location to continue execution
		 *        from.
		 */
		function addReadAhead($function) {
			$this->readAheads[] = $function;
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

		/**
		 * Parse instruction file into instruction array.
		 *
		 * @param $data Data to parse/
		 */
		public static function parseInstrLines($input) {
			$data = array();
			foreach ($input as $lines) {
				if (preg_match('#([a-z]{3}) ([^\s]+)(?: (.*))?#SADi', $lines, $m)) {
					$data[] = array($m[1], array_slice($m, 2));
				}
			}
			return $data;
		}

		/**
		 * Display an instruction as a string.
		 *
		 * @param $instr Instruction to get string representation for.
		 * @return  String version of instruction.
		 */
		public static function instrToString($instr) {
			return $instr[0] . ' [' . implode(' ', $instr[1]) . ']';
		}
	}
