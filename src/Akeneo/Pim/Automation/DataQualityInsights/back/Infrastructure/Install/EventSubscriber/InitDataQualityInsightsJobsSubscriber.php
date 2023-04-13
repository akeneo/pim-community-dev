<?php

declare(strict_types=1);

/*
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::PRE_LOAD_FIXTURES => 'initJobs',
        ];
    }

    public function initJobs(): void
    {
        $this->initializeJobs->initialize();
    }
}
