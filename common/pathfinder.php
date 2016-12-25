<?php
	class PathFinder {
		var $initialState = [];

		var $isAccessible = null;
		var $changeState = null;
		var $stateSorter = null;

		function __construct($grid, $start, $end, $isAccessible = null, $changeState = null, $stateSorter = null) {
			$this->initialState = ['grid' => $grid, 'current' => $start, 'target' => $end, 'steps' => 0, 'previous' => []];

			$this->isAccessible = ($isAccessible != null) ? $isAccessible : function($state, $x, $y) { return false; };
			$this->changeState = ($changeState != null) ? $changeState : function($oldState, $newState) { return $newState; };
			$this->stateSorter = ($stateSorter != null) ? $stateSorter : null;
		}

		function isFinished($state) {
			return ($state['current'] == $state['target']);
		}

		function getOptions($state) {
			$curX = $state['current'][0];
			$curY = $state['current'][1];

			$options = [];
			foreach ([$curX - 1, $curX, $curX + 1] as $x) {
				foreach ([$curY - 1, $curY, $curY + 1] as $y) {
					if (!isset($state['grid'][$y][$x])) { continue; } // Ignore Invalid
					if ($x != $curX && $y != $curY) { continue; } // Ignore Corners
					if ($y == $curY && $x == $curX) { continue; } // Ignore Current

					$new = [$x, $y];
					if (call_user_func($this->isAccessible, $state, $x, $y) && !in_array($new, $state['previous'])) {
						$newState = $state;
						$newState['previous'][] = $newState['current'];
						$newState['current'] = $new;
						$newState['steps']++;
						$newState = call_user_func($this->changeState, $state, $newState);
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

			while (count($states) > 0) {
				$state = array_shift($states);

				if ($maxSteps == -1 && $this->isFinished($state)) {
					$finalState = $state;
					break;
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
