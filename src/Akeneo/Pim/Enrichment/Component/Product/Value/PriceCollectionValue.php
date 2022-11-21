<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollectionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

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
     * {@inheritdoc}
     */
    protected function __construct(
        string $attributeCode,
        ?PriceCollectionInterface $data,
        ?string $scopeCode,
        ?string $localeCode
    ) {
        parent::__construct($attributeCode, $data, $scopeCode, $localeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): ?PriceCollectionInterface
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrice(string $currency): ?ProductPriceInterface
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
    public function hasData(): bool
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
    public function __toString(): string
    {
        $priceStrings = [];
        foreach ($this->data as $price) {
            if (null !== $price->getData()) {
                $priceStrings[] = sprintf('%.2F %s', $price->getData(), $price->getCurrency());
            }
        }

        return implode(', ', $priceStrings);
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(ValueInterface $value): bool
    {
        if (!$value instanceof PriceCollectionValueInterface ||
            $value->getLocaleCode() !== $this->getLocaleCode() ||
            $value->getScopeCode() !== $this->getScopeCode()) {
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
