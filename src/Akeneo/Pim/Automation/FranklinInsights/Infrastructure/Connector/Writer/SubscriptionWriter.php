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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Writer;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\SubscriptionProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Events\ProductSubscribed;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Write\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedData;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SubscriptionWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var ProductSubscriptionRepositoryInterface */
    private $productSubscriptionRepository;

    /** @var SubscriptionProviderInterface */
    private $subscriptionProvider;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * @param SubscriptionProviderInterface $subscriptionProvider
     * @param ProductSubscriptionRepositoryInterface $productSubscriptionRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        SubscriptionProviderInterface $subscriptionProvider,
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->subscriptionProvider = $subscriptionProvider;
        $this->productSubscriptionRepository = $productSubscriptionRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @param ProductSubscriptionRequest[] $items
     *
     * {@inheritdoc}
     */
    public function write(array $items): void
    {
        $collection = $this->subscriptionProvider->bulkSubscribe($items);
        $warnings = $collection->warnings();

        foreach ($items as $request) {
            $productId = $request->getProductId()->toInt();
            $response = $collection->get($productId);
            if (null === $response) {
                if (isset($warnings[$productId])) {
                    // TODO: ask POs for error message
                    $this->stepExecution->addWarning(
                        'akeneo_franklin_insights.entity.product_subscription.constraint.invalid_mapped_values',
                        [],
                        new DataInvalidItem(
                            ['identifier' => $request->getProductIdentifier()]
                        )
                    );
                }
                continue;
            }

            $subscription = $this->buildSubscription($request, $response);
            $this->productSubscriptionRepository->save($subscription);
            $this->stepExecution->incrementSummaryInfo('subscribed');

            $this->eventDispatcher->dispatch(ProductSubscribed::EVENT_NAME, new ProductSubscribed($subscription));
        }
    }

    /**
     * @param ProductSubscriptionRequest $request
     * @param ProductSubscriptionResponse $response
     *
     * @return ProductSubscription
     */
    private function buildSubscription(
        ProductSubscriptionRequest $request,
        ProductSubscriptionResponse $response
    ): ProductSubscription {
        $subscription = new ProductSubscription(
            $request->getProductId(),
            $response->getSubscriptionId(),
            $request->getMappedValues()
        );
        $suggestedData = new SuggestedData($response->getSuggestedData());
        $subscription->setSuggestedData($suggestedData);
        $subscription->markAsMissingMapping($response->isMappingMissing());

        return $subscription;
    }
}
