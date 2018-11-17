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

namespace Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\ValueObject\SuggestedData;

/**
 * Handles a FetchProducts command.
 *
 * It fetches ProductSubscription from Franklin
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class FetchProductsHandler
{
    /** @var DataProviderInterface */
    private $dataProvider;

    /** @var ProductSubscriptionRepositoryInterface */
    private $productSubscriptionRepository;

    /**
     * @param DataProviderFactory $dataProviderFactory
     * @param ProductSubscriptionRepositoryInterface $productSubscriptionRepository
     */
    public function __construct(
        DataProviderFactory $dataProviderFactory,
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository
    ) {
        $this->dataProvider = $dataProviderFactory->create();
        $this->productSubscriptionRepository = $productSubscriptionRepository;
    }

    /**
     * @param FetchProductsCommand $command
     */
    public function handle(FetchProductsCommand $command): void
    {
        // TODO: Calculate last date (from command or fetch from repository) APAI-170

        foreach ($this->dataProvider->fetch() as $subscriptionResponse) {
            $subscription = $this->productSubscriptionRepository->findOneByProductId(
                $subscriptionResponse->getProductId()
            );

            if (null === $subscription) {
                continue;
            }

            $suggestedData = new SuggestedData($subscriptionResponse->getSuggestedData());
            $subscription->setSuggestedData($suggestedData);
            $subscription->markAsMissingMapping($subscriptionResponse->isMappingMissing());
            $this->productSubscriptionRepository->save($subscription);
        }
    }
}
