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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Processor;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedData;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class SuggestedDataProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    /** @var ProductSubscriptionRepositoryInterface */
    private $subscriptionRepository;

    /** @var StepExecution */
    private $stepExecution;

    /**
     * @param ProductSubscriptionRepositoryInterface $subscriptionRepository
     */
    public function __construct(ProductSubscriptionRepositoryInterface $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function process($subscriptionResponse)
    {
        $subscription = $this->subscriptionRepository->findOneByProductId($subscriptionResponse->getProductId());
        if (null === $subscription) {
            throw new InvalidItemException(
                'This product should not be subscribed anymore',
                new DataInvalidItem(['subscriptionId' => $subscriptionResponse->getSubscriptionId()])
            );
        }
        if (true === $subscriptionResponse->isCancelled()) {
            $subscription->markAsCancelled();
        }

        $suggestedData = new SuggestedData($subscriptionResponse->getSuggestedData());
        $subscription->setSuggestedData($suggestedData);
        $subscription->markAsMissingMapping($subscriptionResponse->isMappingMissing());

        return $subscription;
    }

    /**
     * @param StepExecution $stepExecution
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }
}
