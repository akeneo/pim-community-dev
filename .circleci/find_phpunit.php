<?php

declare(strict_types=1);

require 'vendor/autoload.php';

class FindPhpUnitFile extends PHPUnit\TextUI\Command
{
    public function run(array $argv, bool $exit = true): int
    {
        $this->handleArguments($argv);

        $testSuite = $this->arguments['test'];
        $testClasses = [];

        foreach (new \RecursiveIteratorIterator($testSuite->getIterator()) as $test) {
            $testClasses[] = get_class($test);
        }

        $testClasses = array_unique($testClasses);

        foreach ($testClasses as $testClass) {
            $r = new \ReflectionClass($testClass);
            echo $r->getFileName()."\n";
        }

        return 0;
    }
}

FindPhpUnitFile::main();
