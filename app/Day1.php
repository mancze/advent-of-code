<?php

namespace AdventOfCode2016;

use Exception;
use Generator;
use InvalidArgumentException;
use Nette\Utils\Strings;
use Tracy\Dumper;


class Day1Task1Solver extends BaseSolver
{

	public function run() {
		$args = $this->args;

		if ( ! $args) {
			$args[] = "php://stdin";
		}

		$instructions = file_get_contents(array_pop($args));
		$ride = new TaxicabRide();
		$ride->drive($instructions);

		echo "Easter Bunny HQ is {$ride->getDistance()} blocks away.";
	}

}


class Day1Task2Solver extends BaseSolver
{

	public function run() {
		$args = $this->args;

		if ( ! $args) {
			$args[] = "php://stdin";
		}

		$instructions = file_get_contents(array_pop($args));
		$ride = new TaxicabRide();
		$ride->drive($instructions);
		$crossings = $ride->getCrossings();
		$crossing = $crossings->current();
		$crossingDistnace = distance($crossing);

		echo "Easter Bunny HQ is at:" . PHP_EOL;
		Dumper::dump($crossing);

		echo "which is {$crossingDistnace} clicks away." . PHP_EOL;
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

	/** @var int[] Current location. */
	private $location = [0, 0];

	/** @var array[] History of a drive. */
	private $waypoints = [];


	/**
	 * TaxicabDriver constructor.
	 *
	 * @param int $rotation Initial rotation
	 */
	public function __construct(int $rotation = 0) {
		$this->rotation = $rotation;
		$this->pushWaypoint();
	}


	/**
	 * Pushes current location into waypoint history.
	 */
	private function pushWaypoint(): void {
		$this->waypoints[] = $this->location;
	}


	public function drive(string $instructions): void {
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

			$direction = $this->getDirection();

			if ($direction == Direction::NORTH) {
				$this->location[0] += $clicks;
			}
			elseif ($direction == Direction::SOUTH) {
				$this->location[0] -= $clicks;
			}
			elseif ($direction == Direction::EAST) {
				$this->location[1] += $clicks;
			}
			elseif ($direction == Direction::WEST) {
				$this->location[1] -= $clicks;
			}
			else {
				throw new Exception("Invalid direction.");
			}

			$this->pushWaypoint();
		}

		echo "Drive waypoints:" . PHP_EOL;
		Dumper::dump($this->waypoints);

		echo "Final location is:" . PHP_EOL;
		Dumper::dump($this->location);
	}


	private function parseInstructions(string $instructions): array {
		$delimiter = "~";
		$patternParts = [
			$delimiter,
			"(",
			"(" . preg_quote(self::LEFT, $delimiter) . ")|(" . preg_quote(self::RIGHT, $delimiter) . ")",
			")",
			'(\d+)',
			$delimiter,
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
	 *
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
	 *
	 * @return int
	 */
	public function getDistance(): int {
		return distance($this->location);
	}


	/**
	 * Yields all crossing of the drive.
	 *
	 * @return Generator
	 */
	public function getCrossings(): Generator {
		for ($a = 1; $a < count($this->waypoints); ++$a) {
			$formerStart = $this->waypoints[$a - 1];
			$formerEnd = $this->waypoints[$a];

			for ($b = $a + 1; $b < count($this->waypoints); ++$b) {
				$latterStart = $this->waypoints[$b - 1];
				$latterEnd = $this->waypoints[$b];

				$crossing = $this->getCrossing($formerStart, $formerEnd, $latterStart, $latterEnd);

				if ($crossing) {
					yield $crossing;
				}
			}
		}
	}


	private function getCrossing(array $formerStart, array $formerEnd, array $latterStart, array $latterEnd): ?array {
		// naive but dead simple algorithm - test everything
		// no math needed ;)
		foreach ($this->interpolate($formerStart, $formerEnd) as $location) {
			if ($location[0] == $latterStart[0] && $location[0] == $latterEnd[0]) {
				if (isBetween($location[1], $latterStart[1], $latterEnd[1])) {
					return $location;
				}
			}
			elseif ($location[1] == $latterStart[1] && $location[1] == $latterEnd[1]) {
				if (isBetween($location[0], $latterStart[0], $latterEnd[0])) {
					return $location;
				}
			}
		}

		return null;
	}


	private function interpolate(array $start, array $end): Generator {
		$vec = [
			sign($end[0] - $start[0]),
			sign($end[1] - $start[1]),
		];

		$current = $start;
		while ($current != $end) {
			yield $current;

			$current = [
				$current[0] + $vec[0],
				$current[1] + $vec[1],
			];
		}
	}


}


/**
 * Gets the manhattan distance from the [0,0].
 *
 * @param int[] $location
 * @return int
 */
function distance(array $location): int {
	return abs($location[0]) + abs($location[1]);
}


/**
 * Gets the signum of the number
 * @param int $n
 * @return int 1/0/-1
 */
function sign(int $n): int {
	return ($n > 0) - ($n < 0);
}


/**
 * Tests whether value is within given range (bounds included).
 * @param int $value
 * @param int $rangeStart
 * @param int $rangeEnd
 * @return bool
 */
function isBetween(int $value, int $rangeStart, int $rangeEnd): bool {
	$min = min($rangeStart, $rangeEnd);
	$max = max($rangeStart, $rangeEnd);

	return ($value >= $min && $value <= $max);
}
