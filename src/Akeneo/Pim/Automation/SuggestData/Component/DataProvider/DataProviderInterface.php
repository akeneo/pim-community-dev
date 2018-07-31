<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Component\DataProvider;

use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider\SuggestedDataCollectionInterface;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider\SuggestedDataInterface;
use Akeneo\Pim\Automation\SuggestData\Component\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Component\Model\ProductSubscriptionResponse;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
interface DataProviderInterface
{
    /**
     * @param ProductSubscriptionRequest $request
     *
     * @return ProductSubscriptionResponse
     */
    public function subscribe(ProductSubscriptionRequest $request): ProductSubscriptionResponse;

    /**
     * @param ProductInterface[] $products
     *
     * @return SuggestedDataCollectionInterface
     */
    public function bulkPush(array $products): SuggestedDataCollectionInterface;

    public function pull(ProductInterface $product);

    public function bulkPull(array $products);

    public function authenticate(?string $token): bool;

    public function configure(array $config);
}
