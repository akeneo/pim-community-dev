<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber;

use Akeneo\Tool\Bundle\VersioningBundle\Event\PreAdvisementVersionEvent;
use Akeneo\Tool\Bundle\VersioningBundle\Event\PurgeVersionEvents;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber that advances a progress bar during a purge version operation
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeProgressBarAdvancerSubscriber implements EventSubscriberInterface
{
    /** @var ProgressBar */
    protected $progressBar;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [PurgeVersionEvents::PRE_ADVISEMENT  => 'advanceProgressbar'];
    }

    /**
     * Keeps the progress bar in track with the processed versions
     *
     * @param PreAdvisementVersionEvent $preAdvisementVersionEvent
     */
    public function advanceProgressBar(PreAdvisementVersionEvent $preAdvisementVersionEvent)
    {
        if (null !== $this->progressBar) {
            $this->progressBar->advance();
        }
    }

    /**
     * @param ProgressBar $progressBar
     */
    public function setProgressBar(ProgressBar $progressBar)
    {
        $this->progressBar = $progressBar;
    }
}
