<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Infra\DataProvider\Adapter;

use Akeneo\Pim\Automation\SuggestData\Bundle\Infra\DataProvider\SuggestedDataCollectionInterface;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infra\DataProvider\SuggestedDataInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
interface DataProviderAdapterInterface
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

    public function authenticate();

    public function configure(array $config);
}
