<?php

namespace PimEnterprise\Bundle\SuggestDataBundle\Infra\Client;

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\SuggestDataBundle\Client\ClientInterface;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class NullClient implements ClientInterface
{
    /**
     * @param ProductInterface $product
     */
    public function pushProduct(ProductInterface $product)
    {

    }
}
