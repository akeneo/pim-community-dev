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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\Writer;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\SubscriptionProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\Read\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\Write\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\ValueObject\SuggestedData;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SubscriptionWriter implements ItemWriterInterface, StepExecutionAwareInterface, InitializableInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var ProductSubscriptionRepositoryInterface */
    private $productSubscriptionRepository;

    /** @var IdentifiersMappingRepositoryInterface */
    private $identifiersMappingRepository;

    /** @var SubscriptionProviderInterface */
    private $subscriptionProvider;

    /** @var IdentifiersMapping */
    private $identifiersMapping;

    /**
     * @param SubscriptionProviderInterface $subscriptionProvider
     * @param ProductSubscriptionRepositoryInterface $productSubscriptionRepository
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     */
    public function __construct(
        SubscriptionProviderInterface $subscriptionProvider,
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository
    ) {
        $this->subscriptionProvider = $subscriptionProvider;
        $this->productSubscriptionRepository = $productSubscriptionRepository;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        $this->identifiersMapping = $this->identifiersMappingRepository->find();
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items): void
    {
        $collection = $this->subscriptionProvider->bulkSubscribe($items);
        $warnings = $collection->warnings();

        foreach ($items as $item) {
            $productId = $item->getProduct()->getId();
            $response = $collection->get($productId);
            if (null === $response) {
                if (isset($warnings[$productId])) {
                    // TODO: ask POs for error message
                    $this->stepExecution->addWarning(
                        'An error was returned by Franklin during subscription: %error%',
                        ['%error%' => $warnings[$productId]],
                        new DataInvalidItem(
                            ['identifier' => $item->getProduct()->getIdentifier()]
                        )
                    );
                }
                continue;
            }

            $subscription = $this->buildSubscription($item, $response);
            $this->productSubscriptionRepository->save($subscription);
            $this->stepExecution->incrementSummaryInfo('subscribed');
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
            $request->getProduct(),
            $response->getSubscriptionId(),
            $request->getMappedValues($this->identifiersMapping)
        );
        $suggestedData = new SuggestedData($response->getSuggestedData());
        $subscription->setSuggestedData($suggestedData);
        $subscription->markAsMissingMapping($response->isMappingMissing());

        return $subscription;
    }
}
