<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollectionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Product value for "pim_catalog_price_collection" attribute type
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceCollectionValue extends AbstractValue implements PriceCollectionValueInterface
{
    /** @var PriceCollectionInterface|null */
    protected $data;

    /**
     * @param AttributeInterface            $attribute
     * @param string                        $channel
     * @param string                        $locale
     * @param PriceCollectionInterface|null $data
     */
    public function __construct(
        AttributeInterface $attribute,
        $channel,
        $locale,
        PriceCollectionInterface $data = null
    ) {
        $this->setAttribute($attribute);
        $this->setScope($channel);
        $this->setLocale($locale);

        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $currency
     *
     * @return PriceCollectionInterface|null
     */
    public function getPrice($currency)
    {
        foreach ($this->data as $price) {
            if ($price->getCurrency() === $currency) {
                return $price;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasData()
    {
        foreach ($this->data as $price) {
            if (null !== $price->getData()) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $options = [];
        foreach ($this->data as $price) {
            if (null !== $price->getData()) {
                $options[] = sprintf('%.2F %s', $price->getData(), $price->getCurrency());
            }
        }

        return implode(', ', $options);
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(ValueInterface $value)
    {
        if (!$value instanceof PriceCollectionValueInterface ||
            $value->getLocale() !== $this->getLocale() ||
            $value->getScope() !== $this->getScope()) {
            return false;
        }

        $comparedPriceCollection = $value->getData();
        $thisPriceCollection = $this->getData();

        if (null === $thisPriceCollection && null === $comparedPriceCollection) {
            return true;
        }
        if (null === $thisPriceCollection || null === $comparedPriceCollection) {
            return false;
        }
        if ($comparedPriceCollection->count() !== $thisPriceCollection->count()) {
            return false;
        }

        foreach ($comparedPriceCollection as $comparedPrice) {
            $samePriceFound = false;
            foreach ($thisPriceCollection as $thisPrice) {
                if ($thisPrice->isEqual($comparedPrice)) {
                    $samePriceFound = true;

                    break;
                }
            }

            if (!$samePriceFound) {
                return false;
            }
        }

        return true;
    }
}
