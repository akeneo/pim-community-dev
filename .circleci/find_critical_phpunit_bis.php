<?php

use PHPUnit\Framework\TestSuite;
use PHPUnit\Util\Configuration;

require __DIR__ . '/../vendor/autoload.php';

$usage = <<<USAGE

USAGE;

if (null === $argv[1] || null === $argv[2]) {
    echo $usage;
    exit -1;
}

$suiteName = $argv[1];
$criticals = boolval($argv[2]);

$configuration = Configuration::getInstance(__DIR__ . '/../app/phpunit.xml.dist');
$testSuiteConfiguration = $configuration->getTestSuiteConfiguration();

$matchingSuites = array_filter(
    $testSuiteConfiguration->tests(),
    function ($suite) use ($suiteName) {
        return $suiteName === $suite->getName();
    }
);

if (empty($matchingSuites)) {
    throw new \Exception(sprintf('The suite "%s" does not exist!', $suiteName));
}

$suite = array_pop($matchingSuites);

$testClasses = [];
if ($criticals === true) {
    $testClasses = classesInsideGroup($suite, 'critical');
} else {
    $criticalClasses = classesInsideGroup($suite, 'critical');
    $defaultClasses = classesInsideGroup($suite, 'default');
    $testClasses = array_diff($defaultClasses, $criticalClasses);
}

array_map(
    function ($testClass) {
        echo $testClass . "\n";
    },
    $testClasses
);

function classesInsideGroup(TestSuite $suite, string $groupName): array
{
    if (!in_array($groupName, $suite->getGroups())) {
        throw new Exception(sprintf('The group "%s" does not exist in the suite "%s".', $groupName, $suite->getName()));
    }

    $group = $suite->getGroupDetails()[$groupName];

    return array_map(
        function ($suite) {
            return $suite->getName();
        },
        $group
    );
}
