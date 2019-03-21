<?php

$content = file_get_contents("phpunits.xml");

$xml = new SimpleXMLElement($content);
$classesWithCriticalMethods = $xml->xpath("//tests/testCaseClass[testCaseMethod[contains(@groups, 'critical')]]");

$results = array_reduce(
    $classesWithCriticalMethods,
    function (array $carry, SimpleXMLElement $element) {

        $className = $element["name"];
        $methods = [];
        foreach ($element as $method) {
            if (false !== strpos((string)$method["groups"], "critical")) {
                $methods[] = (string)$method["name"];
            }
        }

        $carry[] = [
            "class"   => (string)$className,
            "methods" => $methods,
        ];

        return $carry;
    },
    []
);

var_dump($results);
