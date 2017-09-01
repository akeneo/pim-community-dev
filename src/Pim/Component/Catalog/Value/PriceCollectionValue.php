<?php

namespace Pim\Component\Catalog\Value;

use Pim\Component\Catalog\Model\AbstractValue;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\PriceCollectionInterface;

/**
 * Product value for "pim_catalog_price_collection" attribute type
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceCollectionValue extends AbstractValue implements PriceCollectionValueInterface
{
    /** @var PriceCollectionInterface */
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
}
