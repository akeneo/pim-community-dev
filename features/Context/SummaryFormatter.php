<?php

namespace Context;

use Behat\Behat\DataCollector\LoggerDataCollector;
use Behat\Behat\Event\FeatureEvent;
use Behat\Behat\Formatter\ProgressFormatter;

/**
 * A behat formatter that only prints summary at the end of each feature
 * Used to have nice output for parallelized builds
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SummaryFormatter extends ProgressFormatter
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        $events = parent::getSubscribedEvents();

        // Skip printing output for steps
        unset($events['afterStep']);

        $events['afterFeature'] = 'afterFeature';

        return $events;
    }

    /**
     * Listens to "feature.after" event
     *
     * @param FeatureEvent $event
     */
    public function afterFeature(FeatureEvent $event)
    {
        $fileName     = $event->getFeature()->getFile();
        $relativeName = substr($fileName, strpos($fileName, 'features'));

        $this->writeLn();
        $this->write(sprintf('Executed feature %s', $relativeName));
    }

    /**
     * Prints scenarios summary information.
     *
     * @param LoggerDataCollector $logger suite logger
     */
    protected function printScenariosSummary(LoggerDataCollector $logger)
    {
        parent::printScenariosSummary($logger);

        if ('JENKINS' === getenv('BEHAT_CONTEXT')) {
            $this->write(
                sprintf(
                    "\033[1;37m##glados_scenario##%s##glados_scenario##\033[0m",
                    json_encode($logger->getScenariosStatuses())
                )
            );
        }
    }
}
