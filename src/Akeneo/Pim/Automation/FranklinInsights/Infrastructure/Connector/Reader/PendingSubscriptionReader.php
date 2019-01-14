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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Reader;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Cursor\PendingSubscriptionCursor;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class PendingSubscriptionReader implements ItemReaderInterface, StepExecutionAwareInterface
{
    /** @var ProductSubscriptionRepositoryInterface */
    private $subscriptionRepository;

    /** @var PendingSubscriptionCursor */
    private $pendingSubscriptionCursor;

    private $firstRead = true;

    private $stepExecution;

    /**
     * @param PendingSubscriptionCursor $pendingSubscriptionCursor
     */
    public function __construct(
        PendingSubscriptionCursor $pendingSubscriptionCursor
    ) {
        $this->pendingSubscriptionCursor = $pendingSubscriptionCursor;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $pendingSubscription = null;

        if ($this->pendingSubscriptionCursor->valid()) {
            if (!$this->firstRead) {
                $this->pendingSubscriptionCursor->next();
            }

            $pendingSubscription = $this->pendingSubscriptionCursor->current();
            if (false === $pendingSubscription) {
                return null;
            }
            $this->stepExecution->incrementSummaryInfo('read');
        }

        $this->firstRead = false;

        return $pendingSubscription;
    }

    /**
     * @param StepExecution $stepExecution
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }
}
