<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Install\EventSubscriber;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Install\InitializeJobs;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InitDataQualityInsightsJobsSubscriber implements EventSubscriberInterface
{
    /** @var InitializeJobs */
    private $initializeJobs;

    public function __construct(InitializeJobs $initializeJobs)
    {
        $this->initializeJobs = $initializeJobs;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::PRE_LOAD_FIXTURES => 'initJobs',
        ];
    }

    public function initJobs(InstallerEvent $event): void
    {
        $this->initializeJobs->initialize();
    }
}
