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
use Behat\Behat\Tester\Result\StepResult;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Hook\Tester\Setup\HookedTeardown;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;
use Behat\Testwork\Tester\Result\TestResult;
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
            $this->markScenarioAsFailedWhenThereAreTeardownExceptions($afterScenario['step_events'], $formatter);
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

    /**
     * The common error format of the Junit output file is:
     *    <testcase
     *      name="tests/legacy/features/update/add_association.feature:6"
     *      file="tests/legacy/features/update/add_association.feature:6"
     *      status="failed"
     *      time="3.787"
     *    >
     *       <failure message="Then I should get the following products after apply the following updater to it"></failure>
     *    </testcase>
     *
     * Though, when there is a JS error caught into a hook triggered after a step, the Junit output is:
     *     <testcase
     *          name="tests/legacy/features/category/create_a_category.feature:11"
     *          file="tests/legacy/features/category/create_a_category.feature:11" status="failed" time="5.215">
     *     </testcase>
     *
     * The testcase is considered as failed because there was an exception triggered in the teardown.
     * https://github.com/Behat/Behat/blob/v3.4.3/src/Behat/Testwork/Tester/Result/TestWithSetupResult.php#L67
     *
     * But the failure node is missing, because there are not any step considered as failed, only SUCCESS or SKIPPED.
     * Skipped steps are never printed into a testcase node.
     *
     * https://github.com/Behat/Behat/blob/v3.4.3/src/Behat/Behat/Output/Node/Printer/JUnit/JUnitStepPrinter.php#L63
     *
     * This is problematic, because our current CI (Circle CI) does not consider this scenario as failing despite the testcase status "failed".
     * To consider the test as failed, there should be a failure node inside the testcase node.
     *
     * So, when there is an exception caught into the teardown of a step, we add a failure node.
     *
     */
    private function markScenarioAsFailedWhenThereAreTeardownExceptions(array $stepEvents, Formatter $formatter):void
    {
        $message = 'This scenario has an error not properly catched by behat. It is probably a JS error. Exception: "%s".';
        $failedSteps = array_filter($stepEvents, function (AfterStepTested $afterStepTested) {
            return in_array($afterStepTested->getTestResult()->getResultCode(), [TestResult::FAILED, TestResult::PENDING, StepResult::UNDEFINED]);
        });

        if (count($failedSteps) > 0) {
            return;
        }

        foreach ($stepEvents as $stepEvent) {
            $teardown = $stepEvent->getTeardown();
            if ($teardown instanceof HookedTeardown && !$teardown->isSuccessful()) {
                foreach ($teardown->getHookCallResults() as $result) {
                    if ($result->hasException()) {
                        $formatter->getOutputPrinter()->addTestcaseChild(
                            'failure',
                            ['message' => sprintf($message, $result->getException()->getMessage())]
                        );
                    }
                };
            };
        }
    }
}
