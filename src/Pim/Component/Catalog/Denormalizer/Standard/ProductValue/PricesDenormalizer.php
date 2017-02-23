<?php

namespace Pim\Component\Catalog\Denormalizer\Standard\ProductValue;

use Pim\Component\Catalog\Factory\PriceFactory;
use Pim\Component\Catalog\Model\PriceCollection;

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
    /** @var PriceFactory */
    protected $priceFactory;

    /**
     * @param string[]     $supportedTypes
     * @param PriceFactory $priceFactory
     */
    public function __construct(array $supportedTypes, PriceFactory $priceFactory)
    {
        parent::__construct($supportedTypes);

        $this->priceFactory = $priceFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (empty($data)) {
            return null;
        }

        $prices = new PriceCollection();

        foreach ($data as $priceData) {
            $prices->add($this->priceFactory->createPrice($priceData['amount'], $priceData['currency']));
        }

        return $prices;
    }
}
