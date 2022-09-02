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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Subscriber;

use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasCreated;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasUpdated;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateJobExecutionSummarySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private StepExecution $stepExecution,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ProductWasCreated::class => 'onProductWasCreated',
            ProductWasUpdated::class => 'onProductWasUpdated',
        ];
    }

    public function onProductWasCreated(ProductWasCreated $event): void
    {
        $this->stepExecution->incrementSummaryInfo('create');
    }

    public function onProductWasUpdated(ProductWasUpdated $event): void
    {
        $this->stepExecution->incrementSummaryInfo('process');
    }
}
