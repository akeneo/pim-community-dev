<?php
declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\Adapter;

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\SuggestedDataCollectionInterface;
use PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\SuggestedDataInterface;

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
