<?php

declare(strict_types=1);

namespace Pim\Behat\Extension\PimFormatter\Output\Node\EventListener;

use Behat\Behat\EventDispatcher\Event\AfterFeatureTested;
use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Behat\EventDispatcher\Event\BeforeFeatureTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\EventDispatcher\Event\StepTested;
use Behat\Behat\Output\Node\Printer\FeaturePrinter;
use Behat\Behat\Output\Node\Printer\JUnit\JUnitScenarioPrinter;
use Behat\Behat\Output\Node\Printer\StepPrinter;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;
use Pim\Behat\Extension\PimFormatter\Output\Node\Printer\PimScenarioPrinter;
use Symfony\Component\EventDispatcher\Event;

/**
 * Listens to feature, scenario and step events and calls appropriate printers.
 *
 * This class is unfortunately a copy past of the class Behat\Behat\Output\Node\EventListener\JUnit\JUnitFeatureElementListener
 * because the class is not open to the modification (scenario printer is not an interface).
 * So, this implementation allows to inject our own scenario printer `PimScenarioPrinter`.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @see Behat\Behat\Output\Node\EventListener\JUnit\JUnitFeatureElementListener
 */
final class PimFeatureElementListener implements EventListener
{
    /** @var FeaturePrinter */
    private $featurePrinter;

    /** @var JUnitScenarioPrinter */
    private $scenarioPrinter;

    /** @var StepPrinter */
    private $stepPrinter;

    /** @var FeatureNode */
    private $beforeFeatureTestedEvent;

    /** @var AfterScenarioTested[] */
    private $afterScenarioTestedEvents = [];

    /** @var AfterStepTested[] */
    private $afterStepTestedEvents = [];

    /**
     * @param FeaturePrinter     $featurePrinter
     * @param PimScenarioPrinter $scenarioPrinter
     * @param StepPrinter        $stepPrinter
     */
    public function __construct(FeaturePrinter $featurePrinter, PimScenarioPrinter $scenarioPrinter, StepPrinter $stepPrinter)
    {
        $this->featurePrinter = $featurePrinter;
        $this->scenarioPrinter = $scenarioPrinter;
        $this->stepPrinter = $stepPrinter;
    }

    /**
     * {@inheritdoc}
     */
    public function listenEvent(Formatter $formatter, Event $event, $eventName)
    {
        if ($event instanceof ScenarioTested) {
            $this->captureScenarioEvent($event);
        }

        if ($event instanceof StepTested) {
            $this->captureStepEvent($event);
        }

        $this->captureFeatureOnBeforeEvent($event);
        $this->printFeatureOnAfterEvent($formatter, $event);
    }

    /**
     * Prints the feature on AFTER event.
     *
     * @param Formatter $formatter
     * @param Event     $event
     */
    public function printFeatureOnAfterEvent(Formatter $formatter, Event $event) : void
    {
        if (!$event instanceof AfterFeatureTested) {
            return;
        }

        $this->featurePrinter->printHeader($formatter, $this->beforeFeatureTestedEvent);

        foreach ($this->afterScenarioTestedEvents as $afterScenario) {
            $afterScenarioTested = $afterScenario['event'];
            $this->scenarioPrinter->printOpenTag($formatter, $afterScenarioTested->getFeature(), $afterScenarioTested->getScenario(), $afterScenarioTested->getTestResult());

            foreach ($afterScenario['step_events'] as $afterStepTested) {
                $this->stepPrinter->printStep($formatter, $afterScenarioTested->getScenario(), $afterStepTested->getStep(), $afterStepTested->getTestResult());
            }
        }

        $this->featurePrinter->printFooter($formatter, $event->getTestResult());
        $this->afterScenarioTestedEvents = [];
    }

    /**
     * Captures scenario tested event.
     *
     * @param ScenarioTested $event
     */
    private function captureScenarioEvent(ScenarioTested $event) : void
    {
        if ($event instanceof AfterScenarioTested) {
            $this->afterScenarioTestedEvents[$event->getScenario()->getLine()] = [
                'event' => $event,
                'step_events' => $this->afterStepTestedEvents,
            ];

            $this->afterStepTestedEvents = [];
        }
    }

    /**
     * Captures feature on BEFORE event.
     *
     * @param Event $event
     */
    private function captureFeatureOnBeforeEvent(Event $event) : void
    {
        if (!$event instanceof BeforeFeatureTested) {
            return;
        }

        $this->beforeFeatureTestedEvent = $event->getFeature();
    }

    /**
     * Captures step tested event.
     *
     * @param StepTested $event
     */
    private function captureStepEvent(StepTested $event) : void
    {
        if ($event instanceof AfterStepTested) {
            $this->afterStepTestedEvents[$event->getStep()->getLine()] = $event;
        }
    }
}
