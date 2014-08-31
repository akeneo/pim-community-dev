<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;

/**
 * Price collection flat denormalizer used for attribute type:
 * - pim_catalog_price_collection
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class PricesDenormalizer extends AbstractValueDenormalizer
{
    /** @var ProductBuilder */
    protected $productBuilder;

    /**
     * @param ProductBuilder $productBuilder
     */
    public function __construct(array $supportedTypes, ProductBuilder $productBuilder)
    {
        parent::__construct($supportedTypes);

        $this->productBuilder = $productBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $value    = $context['value'];
        $currency = $context['price_currency'];

        $priceValue = $this->productBuilder->addPriceForCurrency($value, $currency);
        $priceValue->setCurrency($currency);
        $priceValue->setData($data);

        return $priceValue;
    }
}
