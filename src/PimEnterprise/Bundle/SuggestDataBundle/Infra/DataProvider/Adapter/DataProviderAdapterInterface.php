<?php
declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\Adapter;

use Pim\Component\Catalog\Model\ProductInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
interface DataProviderAdapterInterface
{
    /**
     * @param ProductInterface $product
     *
     * @return string
     */
    public function push(ProductInterface $product): string;

    /**
     * @param ProductInterface[] $products
     *
     * @return string
     */
    public function bulkPush(array $products): string;

    public function pull(ProductInterface $product);

    public function bulkPull(array $products);

    public function authenticate();

    public function configure(array $config);
}
