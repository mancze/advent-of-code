<?php

namespace AdventOfCode2016;

use Exception;
use InvalidArgumentException;
use Nette\Utils\Strings;
use Tracy\Dumper;


class Day1Task1 implements IRunnable
{

	public function run() {
		$instructions = file_get_contents("php://stdin");
		$ride = new TaxicabRide();
		$ride->drive($instructions);

		echo "Easter Bunny HQ is {$ride->getDistance()} blocks away.";
	}

}


/**
 * Pseudo-enumeration of cardinal directions.
 */
class Direction
{

	const NORTH = 0;
	const EAST = 1;
	const SOUTH = 2;
	const WEST = 3;


	private function __construct() {
		throw new Exception("Pseudo-enumeration class");
	}


	public static function getValues() {
		return [
			self::NORTH,
			self::EAST,
			self::SOUTH,
			self::WEST,
		];
	}

}


class TaxicabRide
{

	const LEFT = "L";
	const RIGHT = "R";

	/** @var int Current rotation. Rotations are added so to get direction use modulo. */
	private $rotation = 0;

	/** @var int[] Clicks in given direction */
	private $clicks = [0, 0, 0, 0];


	/**
	 * TaxicabDriver constructor.
	 *
	 * @param int $rotation Initial rotation
	 */
	public function __construct(int $rotation = 0) {
		$this->rotation = $rotation;
	}


	public function drive(string $instructions) : void {
		if ( ! $instructions) {
			throw new InvalidArgumentException("instructions");
		}

		// parse instructions into steps
		$parsedInstructions = $this->parseInstructions($instructions);
		echo "Parsed instructions are:" . PHP_EOL;
		Dumper::dump($parsedInstructions);

		// process steps
		foreach ($parsedInstructions as $step) {
			[$rotation, $clicks] = $step;

			$this->rotation += ($rotation == self::RIGHT ? 1 : -1);
			$this->clicks[$this->getDirection()] += $clicks;
		}

		echo "Final clicks are:" . PHP_EOL;
		Dumper::dump($this->clicks);
	}


	private function parseInstructions(string $instructions) : array {
		$delimiter = "~";
		$patternParts = [
			$delimiter,
			"(",
			"(" . preg_quote(self::LEFT, $delimiter) . ")|(" . preg_quote(self::RIGHT, $delimiter) . ")",
			")",
			'(\d+)',
			$delimiter
		];
		$pattern = implode("", $patternParts);

		$matches = Strings::matchAll($instructions, $pattern);
		$result = [];

		if ($matches) {
			foreach ($matches as $match) {
				$result[] = [$match[1], $match[4]];
			}
		}

		return $result;
	}


	/**
	 * Gets the current cardinal direction.
	 * @return int One of Direction::* constants.
	 */
	public function getDirection(): int {
		$directionCount = count(Direction::getValues());
		$direction = ($this->getRotation() % $directionCount);

		if ($direction < 0) {
			$direction += $directionCount;
		}

		return $direction;
	}


	/**
	 * @return int Absolute rotation.
	 */
	public function getRotation(): int {
		return $this->rotation;
	}


	/**
	 * Gets the manhattan distance from the [0,0].
	 * @return int
	 */
	public function getDistance(): int {
		return abs($this->clicks[Direction::NORTH] - $this->clicks[Direction::SOUTH]) +
			abs($this->clicks[Direction::EAST] - $this->clicks[Direction::WEST]);
	}

}
