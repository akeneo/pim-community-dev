<?php

namespace PimEnterprise\Bundle\SuggestDataBundle\Client;

use Pim\Component\Catalog\Model\ProductInterface;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
interface ClientInterface
{
    public function pushProduct(ProductInterface $product);
}
