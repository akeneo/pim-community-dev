<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider\Adapter;

use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider\SuggestedDataCollectionInterface;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider\SuggestedDataInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
interface DataProviderInterface
{
    /**
     * @param ProductInterface $product
     *
     * @return SuggestedDataInterface
     */
    public function push(ProductInterface $product): SuggestedDataInterface;

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
