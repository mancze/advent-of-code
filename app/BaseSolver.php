<?php

namespace AdventOfCode2016;


abstract class BaseSolver implements IRunnable
{

	/** @var array Optional arguments given to a solver. */
	protected $args;


	public function __construct(array $args = []) {
		$this->args = $args;
	}

}
