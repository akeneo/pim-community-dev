<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer\ProductValue;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PricesDenormalizer implements DenormalizerInterface
{
    /** @var array */
    protected $supportedTypes = array('pim_catalog_price_collection');

    /** @var ProductBuilder */
    protected $productBuilder;

    /**
     * @param ProductBuilder $productBuilder
     */
    public function __construct(ProductBuilder $productBuilder)
    {
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

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return in_array($type, $this->supportedTypes) && 'csv' === $format;
    }
}
