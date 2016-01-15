<?php

namespace Context\Formatter;

use Behat\Behat\Event\EventInterface;
use Behat\Behat\Event\OutlineExampleEvent;
use Behat\Behat\Formatter\JUnitFormatter as BehatJUnitFormatter;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioNode;

class JUnitFormatter extends BehatJUnitFormatter
{
    protected function printTestCase(ScenarioNode $scenario, $time, EventInterface $event)
    {
        $className = $this->formatClassname($scenario->getFeature());
        $name      = $scenario->getTitle();
        if ($event instanceof OutlineExampleEvent) {
            $name .= sprintf(', Ex #%d', $event->getIteration() + 1);
        }
        $caseStats = sprintf('classname="%s" name="%s" time="%F" assertions="%d"',
            htmlspecialchars($className),
            htmlspecialchars($name),
            $time,
            $this->scenarioStepsCount
        );

        $xml = "    <testcase $caseStats>\n";

        foreach ($this->exceptions as $exception) {
            $error          = $this->exceptionToString($exception);
            $elemType       = $this->getElementType($event->getResult());
            $elemAttributes = '';
            if ('skipped' !== $elemType) {
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

    /**
     * @param FeatureNode $feature
     *
     * @return string
     */
    private function formatClassname(FeatureNode $feature)
    {
        $filepath  = htmlspecialchars($feature->getFile());
        $classname = substr($filepath, strpos($filepath, 'features/') + strlen('features/'));

        return str_replace('/', '__', $classname);
    }
}
