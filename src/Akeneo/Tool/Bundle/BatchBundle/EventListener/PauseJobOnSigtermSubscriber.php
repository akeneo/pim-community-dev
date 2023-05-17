<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PauseJobOnSigtermSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly FeatureFlags $featureFlags,
        private readonly LoggerInterface $logger
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EventInterface::BEFORE_JOB_EXECUTION => 'onBeforeJobExecution',
        ];
    }

    public function onBeforeJobExecution(JobExecutionEvent $event): void
    {
        if (!$this->featureFlags->isEnabled('pause_jobs')) {
            return;
        }

        pcntl_signal(\SIGTERM, function () use ($event) {
            $this->logger->info('Received SIGTERM signal.', [
                'job_execution_id' => $event->getJobExecution()->getId()
            ]);
        });
    }
}
