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
use Akeneo\Pim\Automation\SuggestData\Domain\Model\SuggestedData;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;

/**
 * Handles a FetchProducts command
 *
 * It fetches ProductSubscription from on PIM.ai
 *
 * @author    Romain Monceau <romain@akeneo.com>
 */
class FetchProductsHandler
{
    /** @var DataProviderFactory */
    private $dataProviderFactory;

    /** @var ProductSubscriptionRepositoryInterface */
    private $productSubscriptionRepository;

    /**
     * @param DataProviderFactory $dataProviderFactory
     * @param ProductSubscriptionRepositoryInterface $productSubscriptionRepository
     */
    public function __construct(
        DataProviderFactory $dataProviderFactory,
        ProductSubscriptionRepositoryInterface $productSusbcriptionRepository
    ) {
        $this->dataProviderFactory = $dataProviderFactory;
        $this->productSubscriptionRepository = $productSusbcriptionRepository;
    }

    /**
     * @param FetchProductsCommand $command
     */
    public function handle(FetchProductsCommand $command): void
    {
        // TODO: Calculate last date (from command or fetch from repository) APAI-170

        // TODO: Deal with many pages (APAI-92)
        $dataProvider = $this->dataProviderFactory->create();
        $subscribedResponses = $dataProvider->fetch();

        foreach ($subscribedResponses->responses() as $subscriptionResponse) {
            $subscription = $this->productSubscriptionRepository->findOneByProductId(
                $subscriptionResponse->getProductId()
            );

            $suggestedData = new SuggestedData($subscriptionResponse->getSuggestedData());
            $subscription->setSuggestedData($suggestedData);

            $this->productSubscriptionRepository->save($subscription);
        }
    }
}
