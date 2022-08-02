<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\InstallerBundle\Event\Subscriber;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Akeneo\Tool\Bundle\BatchBundle\JobExecution\CreateJobExecutionHandler;
use Akeneo\Tool\Bundle\BatchBundle\JobExecution\ExecuteJobExecutionHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RefreshVersioningSubscriber implements EventSubscriberInterface
{
    private const JOB_CODE = 'versioning_refresh';

    public function __construct(
        private ExecuteJobExecutionHandler $jobExecutionRunner,
        private CreateJobExecutionHandler $jobExecutionFactory,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_LOAD_FIXTURES => [
                ['refreshVersioning', 200],
            ],
        ];
    }

    public function refreshVersioning(): void
    {
        $jobExecution = $this->jobExecutionFactory->createFromBatchCode(self::JOB_CODE, [], null);
        $this->jobExecutionRunner->executeFromJobExecutionId($jobExecution->getId());
    }
}
