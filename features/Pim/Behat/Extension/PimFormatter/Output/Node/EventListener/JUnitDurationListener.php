<?php

declare(strict_types=1);

namespace Pim\Behat\Extension\PimFormatter\Output\Node\EventListener;

use Behat\Behat\EventDispatcher\Event\AfterFeatureTested;
use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\BeforeFeatureTested;
use Behat\Behat\EventDispatcher\Event\BeforeScenarioTested;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\KeywordNodeInterface;
use Behat\Gherkin\Node\ScenarioLikeInterface;
use Behat\Testwork\Counter\Timer;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;
use Symfony\Component\EventDispatcher\Event;

/**
 * Allows to display duration in Junit scenario results.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class JUnitDurationListener implements EventListener
{
    private $scenarioTimerStore = [];
    private $featureTimerStore = [];
    private $resultStore = [];
    private $featureResultStore = [];

    /**
     * {@inheritdoc}
     */
    public function listenEvent(Formatter $formatter, Event $event, $eventName)
    {
        $this->captureBeforeScenarioEvent($event);
        $this->captureBeforeFeatureTested($event);
        $this->captureAfterScenarioEvent($event);
        $this->captureAfterFeatureEvent($event);
    }

    /**
     * @param ScenarioLikeInterface $scenario
     *
     * @return int
     */
    public function getDuration(ScenarioLikeInterface $scenario): float
    {
        $key = $this->getHash($scenario);

        return array_key_exists($key, $this->resultStore) ? $this->resultStore[$key] : '';
    }

    /**
     * @param FeatureNode $feature
     *
     * @return int
     */
    public function getFeatureDuration(FeatureNode $feature): float
    {
        $key = $this->getHash($feature);

        return array_key_exists($key, $this->featureResultStore) ? $this->featureResultStore[$key] : '';
    }

    /**
     * @param Event $event
     */
    private function captureBeforeFeatureTested(Event $event): void
    {
        if (!$event instanceof BeforeFeatureTested) {
            return;
        }
        $this->featureTimerStore[$this->getHash($event->getFeature())] = $this->startTimer();
    }

    /**
     * @param Event $event
     */
    private function captureBeforeScenarioEvent(Event $event): void
    {
        if (!$event instanceof BeforeScenarioTested) {
            return;
        }
        $this->scenarioTimerStore[$this->getHash($event->getScenario())] = $this->startTimer();
    }

    /**
     * @param Event $event
     */
    private function captureAfterScenarioEvent(Event $event): void
    {
        if (!$event instanceof AfterScenarioTested) {
            return;
        }
        $key = $this->getHash($event->getScenario());
        $timer = $this->scenarioTimerStore[$key];
        if ($timer instanceof Timer) {
            $timer->stop();
            $this->resultStore[$key] = round($timer->getTime());
        }
    }

    /**
     * @param Event $event
     */
    private function captureAfterFeatureEvent(Event $event): void
    {
        if (!$event instanceof AfterFeatureTested) {
            return;
        }
        $key = $this->getHash($event->getFeature());
        $timer = $this->featureTimerStore[$key];
        if ($timer instanceof Timer) {
            $timer->stop();
            $this->featureResultStore[$key] = round($timer->getTime());
        }
    }

    /**
     * @param KeywordNodeInterface $node
     *
     * @return string
     */
    private function getHash(KeywordNodeInterface $node): string
    {
        return spl_object_hash($node);
    }

    /**
     * @return Timer
     */
    private function startTimer(): Timer
    {
        $timer = new Timer();
        $timer->start();

        return $timer;
    }
}
