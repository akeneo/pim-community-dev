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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Cursor\PendingSubscriptionCursor;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class PendingSubscriptionReader implements ItemReaderInterface, StepExecutionAwareInterface
{
    /** @var PendingSubscriptionCursor */
    private $subscriptionCursor;

    /** @var StepExecution */
    private $stepExecution;

    /**
     * @param PendingSubscriptionCursor $subscriptionCursor
     */
    public function __construct(PendingSubscriptionCursor $subscriptionCursor)
    {
        $this->subscriptionCursor = $subscriptionCursor;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $pendingSubscription = null;
        $this->subscriptionCursor->next();

        if ($this->subscriptionCursor->valid()) {
            $pendingSubscription = $this->subscriptionCursor->current();
            if (null === $pendingSubscription) {
                return null;
            }
            $this->stepExecution->incrementSummaryInfo('read');
        }

        return $pendingSubscription;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }
}
