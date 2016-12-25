<?php
	class PathFinder {
		var $initialState = [];

		var $isAccessible = null;
		var $changeState = null;
		var $stateSorter = null;

		var $hooks = array();

		function __construct($grid, $start, $end, $isAccessible = null) {
			$this->initialState = ['grid' => $grid, 'current' => $start, 'target' => $end, 'steps' => 0, 'previous' => []];

			$this->hooks['isAccessible'] = ($isAccessible != null) ? $isAccessible : function($state, $x, $y) { return false; };

			$this->hooks['isValidLocation'] = function ($state, $x, $y) {
				list($curX, $curY) = $state['current'];
				if (!isset($state['grid'][$y][$x])) { return FALSE; } // Ignore Invalid
				if ($x != $curX && $y != $curY) { return FALSE; } // Ignore Corners
				if ($y == $curY && $x == $curX) { return FALSE; } // Ignore Current
				return TRUE;
			};
		}

		// Valid Hooks:
		// 'changeState' => Called after finding a new state.
		//                  Gets passed [$oldState, $newState].
		//                  $newState is replaced with the return value before
		//                  being added to the possible options from getOptions();
		//
		// 'isValidLocation' => Called to check if a position is a valid location
		//                      to move to from the current location.
		//                      Gets passed [$state, $x, $y].
		//                      Default implementation assumes UDLR are valid.
		//                      Return true if valid, else false.
		//
		// 'isAccessible' => Called to check if a position is accessible.
		//                    Gets passed [$state, $x, $y].
		//                    Default implementation assumes no positions are
		//                    accessible.
		//                    Return true if accessible, else false.
		//
		// 'solveStartState'  => Called before we begin solving.
		//                       Gets passed [$beginState]
		//
		// 'solveNextState' => Called when we are checking a non-finished state.
		//                     Gets passed [$state, $vistedLocations]
		//
		// 'solveFinishedState' => Called when we are at a finished state.
		//                         Gets passed [$finalState, $vistedLocations]
		function setHook($hookPoint, $function) {
			$this->hooks[$hookPoint] = $function;
		}

		function isFinished($state) {
			return ($state['current'] == $state['target']);
		}

		function getOptions($state) {
			list($curX, $curY) = $state['current'];

			$options = [];
			foreach ([$curX - 1, $curX, $curX + 1] as $x) {
				foreach ([$curY - 1, $curY, $curY + 1] as $y) {
					if (!call_user_func($this->hooks['isValidLocation'], $state, $x, $y)) { continue; }

					$new = [$x, $y];
					if (call_user_func($this->hooks['isAccessible'], $state, $x, $y) && !in_array($new, $state['previous'])) {
						$newState = $state;
						$newState['previous'][] = $newState['current'];
						$newState['current'] = $new;
						$newState['steps']++;

						if (isset($this->hooks['changeState'])) {
							$newState = call_user_func($this->hooks['changeState'], $state, $newState);
						}

						$options[] = $newState;
					}
				}
			}

			return $options;
		}

		function solveMaze($maxSteps = -1) {
			$beginState = $this->initialState;

			$visted = [$beginState['current']];
			$states = [$beginState];

			$finalState = FALSE;

			if (isset($this->hooks['solveStartState'])) { call_user_func($this->hooks['solveStartState'], $beginState); }

			while (count($states) > 0) {
				$state = array_shift($states);

				if ($maxSteps == -1 && $this->isFinished($state)) {
					$finalState = $state;
					if (isset($this->hooks['solveFinishedState'])) { call_user_func($this->hooks['solveFinishedState'], $state, $visted); }
					break;
				} else {
					if (isset($this->hooks['solveNextState'])) { call_user_func($this->hooks['solveNextState'], $state, $visted); }
				}

				$options = $this->getOptions($state);
				foreach ($options as $opt) {
					if (!in_array($opt['current'], $visted) && ($maxSteps <= 0 || $opt['steps'] <= $maxSteps)) {
						$visted[] = $opt['current'];
						$states[] = $opt;
					}
				}

				if ($this->stateSorter != null) { uasort($states, $this->stateSorter); }

			}

			return [$finalState, $visted];
		}
	}
