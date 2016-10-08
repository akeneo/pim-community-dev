<?php

namespace Pim\Component\Catalog\Denormalizer\Structured\ProductValue;

use Akeneo\Component\Localization\Localizer\LocalizerInterface;
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

    /** @var LocalizerInterface */
    protected $localizer;

    /**
     * @param string[]           $supportedTypes
     * @param LocalizerInterface $localizer
     * @param string             $productPriceClass
     */
    public function __construct(array $supportedTypes, LocalizerInterface $localizer, $productPriceClass)
    {
        parent::__construct($supportedTypes);

        $this->localizer = $localizer;
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
            $data = $this->localizer->localize($priceData['amount'], $context);
            $prices->add(new $this->productPriceClass($data, $priceData['currency']));
        }

        return $prices;
    }
}
