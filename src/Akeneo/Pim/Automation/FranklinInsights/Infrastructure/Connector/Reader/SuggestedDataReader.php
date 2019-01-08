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

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\SubscriptionProviderInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class SuggestedDataReader implements ItemReaderInterface, InitializableInterface, StepExecutionAwareInterface
{
    /** @var SubscriptionProviderInterface */
    private $subscriptionProvider;

    /** @var \Iterator */
    private $subscriptionsCursor;

    /** @var StepExecution */
    private $stepExecution;

    /** @var bool */
    private $firstRead = true;

    /**
     * @param SubscriptionProviderInterface $subscriptionProvider
     */
    public function __construct(SubscriptionProviderInterface $subscriptionProvider)
    {
        $this->subscriptionProvider = $subscriptionProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        $this->subscriptionsCursor = $this->subscriptionProvider->fetch();
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $suggestedData = null;

        if ($this->subscriptionsCursor->valid()) {
            if (!$this->firstRead) {
                $this->subscriptionsCursor->next();
            }

            $suggestedData = $this->subscriptionsCursor->current();
            if (false === $suggestedData) {
                return null;
            }
            $this->stepExecution->incrementSummaryInfo('read');
        }

        $this->firstRead = false;

        return $suggestedData;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }
}
