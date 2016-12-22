<?php

use Nette\Caching\Storages\MemoryStorage;
use Nette\Loaders\RobotLoader;
use Nette\Utils\Strings;


// initialize 3rd party libraries
require __DIR__ . "/../vendor/autoload.php";

// initialize robot loader
$loader = new RobotLoader();
$loader->addDirectory(__DIR__);
$loader->addDirectory(__DIR__ . "/../library");
$loader->setCacheStorage(new MemoryStorage());
$loader->register();


// process
array_shift($argv);
$command = array_shift($argv);
$matches = Strings::match($command, '~(\d+)[^\d]*(\d+)~');

if ( ! $matches) {
	throw new Exception("Unexpected command {$command}");
}

$solverName = "AdventOfCode2016\\Day{$matches[1]}Task{$matches[2]}Solver";

if ( ! class_exists($solverName)) {
	throw new Exception("Solver \"{$solverName}\" does not exist.");
}

$solver = new $solverName($argv);

return $solver;
