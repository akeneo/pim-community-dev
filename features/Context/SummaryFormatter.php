<?php

namespace Context;

use Behat\Behat\Formatter\ProgressFormatter;
use Behat\Behat\Event\FeatureEvent;

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
        $fileName = $event->getFeature()->getFile();
        $relativeName = substr($fileName, strpos($fileName, 'features'));

        $this->write(sprintf('Executed feature %s', $relativeName));
    }
}
