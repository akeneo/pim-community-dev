<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\EventSubscriber;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Event\FileCannotBeExported;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateJobExecutionStorageSummarySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private JobExecution $jobExecution,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FileCannotBeExported::class => 'onfileCannotBeExported',
        ];
    }

    public function onfileCannotBeExported(FileCannotBeExported $event): void
    {
        $this->jobExecution->addFailureException(new \Exception($event->getReason()));
    }
}
