<?php

namespace PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\Adapter;

use Pim\Component\Catalog\Model\ProductInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
interface DataProviderAdapterInterface
{

    public function push(ProductInterface $product): string;

    public function bulkPush(array $products);

    public function pull(ProductInterface $product);

    public function bulkPull(array $products);

    public function authenticate();

    public function configure(array $config);
}
