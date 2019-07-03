<?php

use PHPUnit\Util\Configuration;

require __DIR__.'/../vendor/autoload.php';

$configuration = Configuration::getInstance(__DIR__ . '/../app/phpunit.xml.dist');
$testSuite = $configuration->getTestSuiteConfiguration();

$suites = $testSuite->tests();
/** @var \PHPUnit\Framework\TestSuite $suite */
foreach ($suites as $suite) {
    echo "\n\n\n-------------------\n";
    $tests = $suite->tests();
    foreach ($tests as $test) {
        var_dump($test);
    }
}
