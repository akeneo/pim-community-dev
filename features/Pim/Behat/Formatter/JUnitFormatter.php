<?php

namespace Pim\Behat\Formatter;

use Behat\Behat\Event\EventInterface;
use Behat\Behat\Event\OutlineExampleEvent;
use Behat\Behat\Formatter\JUnitFormatter as BaseFormatter;
use Behat\Gherkin\Node\ScenarioNode;

/**
 * Formatter to display the path of scenarios instead of the name of the scenario in the CI.
 * Example:  "feature/foo/bar:6"
 */
class JUnitFormatter extends BaseFormatter
{
    /**
     * Prints testcase.
     *
     * @param ScenarioNode   $scenario
     * @param float          $time
     * @param EventInterface $event
     */
    protected function printTestCase(ScenarioNode $scenario, $time, EventInterface $event)
    {
        $className = $scenario->getFile() . ':' . $scenario->getLine();
        $position = strpos($className, '/features');
        $className = substr($className, $position + 1);

        $name = $scenario->getTitle();
        $name .= $event instanceof OutlineExampleEvent
            ? ', Ex #' . ($event->getIteration() + 1)
            : '';
        $caseStats = sprintf('classname="%s" name="%s" time="%F" assertions="%d"',
            htmlspecialchars($className),
            htmlspecialchars($name),
            $time,
            $this->scenarioStepsCount
        );

        $xml = "    <testcase $caseStats>\n";

        foreach ($this->exceptions as $exception) {
            $error = $this->exceptionToString($exception);
            $elemType = $this->getElementType($event->getResult());
            $elemAttributes = '';
            if ($elemType !== 'skipped') {
                $elemAttributes = sprintf(
                    'message="%s" type="%s"',
                    htmlspecialchars($error),
                    $this->getResultColorCode($event->getResult())
                );
            }

            $xml .= sprintf(
                '        <%s %s>',
                $elemType,
                $elemAttributes
            );
            $exception = str_replace(['<![CDATA[', ']]>'], '', (string) $exception);
            $xml .= sprintf(
                "<![CDATA[\n%s\n]]></%s>\n",
                $exception,
                $elemType
            );
        }
        $this->exceptions = [];

        $xml .= "    </testcase>";

        $this->testcases[] = $xml;
    }
}
