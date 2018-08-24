<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Application\ProductFetch\Command;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class FetchProductsHandler
{
    /** @var DataProviderFactory */
    private $dataProviderFactory;

    /**
     * @param DataProviderFactory $dataProviderFactory
     * @param ProductSubscriptionRepositoryInterface $productSubscriptionRepository
     */
    public function __construct(
        DataProviderFactory $dataProviderFactory,
        ProductSusbcriptionRepositoryInterface $productSusbcriptionRepository
    ) {
        $this->dataProviderFactory = $dataProviderFactory;
        $this->productSubscriptionRepository = $productSusbcriptionRepository;
    }

    /**
     * @param FetchProductsCommand $command
     */
    public function handle(FetchProductsCommand $command): void
    {
        // TODO: Calculate last date (from command or fetch from repository)

        // TODO: Deal with many pages
        $dataProvider = $this->dataProviderFactory->create();
        $subscribedResponses = $dataProvider->fetch();

        // TODO: Store fetched data in DB
        foreach ($subscribedResponses as $subscriptionResponse) {
            /*$subscription = $this->findOrCreateSubscription(
                $product,
                $subscriptionResponse->getSubscriptionId()
            );*/
            $subscription->setSuggestedData($subscriptionResponse->getSuggestedData());
        }

    }
}
