<?php

namespace PimEnterprise\Bundle\SuggestDataBundle\DataProvider;
use Pim\Component\Catalog\Model\ProductInterface;


/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class Subscriber
{
    public function __construct()
    {
    }

    public function subcribe(ProductInterface $product)
    {
        //$this->
        // Prepares data to send (Adapter + Mapping)

        // Sends data we want to subscribe on
        $this->client->pushProduct();
    }
}
