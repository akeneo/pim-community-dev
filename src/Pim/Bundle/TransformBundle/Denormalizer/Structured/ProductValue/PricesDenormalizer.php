<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Price collection denormalizer used for attribute type:
 * - pim_catalog_price_collection
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PricesDenormalizer extends AbstractValueDenormalizer
{
    /** @var string */
    protected $productPriceClass;

    /**
     * @param string[] $supportedTypes
     * @param string   $productPriceClass
     */
    public function __construct(array $supportedTypes, $productPriceClass)
    {
        parent::__construct($supportedTypes);

        $this->productPriceClass = $productPriceClass;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (empty($data)) {
            return null;
        }

        $prices = new ArrayCollection();

        foreach ($data as $priceData) {
            $prices->add(new $this->productPriceClass($priceData['data'], $priceData['currency']));
        }

        return $prices;
    }
}
