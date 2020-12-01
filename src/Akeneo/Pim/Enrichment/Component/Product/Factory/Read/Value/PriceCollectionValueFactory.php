<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class PriceCollectionValueFactory implements ReadValueFactory
{
    public function create(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        $this->validate($attribute, $data);

        $sortedData = $this->sortByCurrency($data);
        $attributeCode = $attribute->code();

        $prices = new PriceCollection();

        foreach ($sortedData as $price) {
            $prices->add(new ProductPrice($price['amount'], $price['currency']));
        }

        if ($attribute->isLocalizableAndScopable()) {
            return PriceCollectionValue::scopableLocalizableValue($attributeCode, $prices, $channelCode, $localeCode);
        }

        if ($attribute->isScopable()) {
            return PriceCollectionValue::scopableValue($attributeCode, $prices, $channelCode);
        }

        if ($attribute->isLocalizable()) {
            return PriceCollectionValue::localizableValue($attributeCode, $prices, $localeCode);
        }

        return PriceCollectionValue::value($attributeCode, $prices);
    }

    public function supportedAttributeType(): string
    {
        return AttributeTypes::PRICE_COLLECTION;
    }

    private function validate(Attribute $attribute, $data): void
    {
        if (!\is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected(
                $attribute->code(),
                static::class,
                $data
            );
        }

        foreach ($data as $price) {
            if (!\is_array($price)) {
                throw InvalidPropertyTypeException::arrayOfArraysExpected(
                    $attribute->code(),
                    static::class,
                    $data
                );
            }

            if (!array_key_exists('amount', $price)) {
                throw InvalidPropertyTypeException::arrayKeyExpected(
                    $attribute->code(),
                    'amount',
                    static::class,
                    $data
                );
            }

            if (!array_key_exists('currency', $price)) {
                throw InvalidPropertyTypeException::arrayKeyExpected(
                    $attribute->code(),
                    'currency',
                    static::class,
                    $data
                );
            }
        }
    }

    /**
     * Sorts the array of prices data by their currency.
     *
     * For example:
     *
     * [
     *     [
     *         'amount'   => 20,
     *         'currency' => 'USD',
     *     ],
     *     [
     *         'amount'   => 100,
     *         'currency' => 'EUR',
     *     ],
     * ]
     *
     * will become:
     *
     * [
     *     [
     *         'amount'   => 100,
     *         'currency' => 'EUR',
     *     ],
     *     [
     *         'amount'   => 20,
     *         'currency' => 'USD',
     *     ],
     * ]
     *
     * @param array $arrayPrices
     *
     * @return array
     */
    private function sortByCurrency(array $arrayPrices): array
    {
        $amounts = [];
        $currencies = [];

        foreach ($arrayPrices as $price) {
            $amounts[] = $price['amount'];
            $currencies[] = $price['currency'];
        }

        $sort = array_multisort($currencies, SORT_ASC, $amounts, SORT_ASC, $arrayPrices);

        if (false === $sort) {
            throw new \LogicException(
                sprintf('Impossible to perform multisort on the following array: %s', json_encode($arrayPrices)),
                0,
                static::class
            );
        }

        return $arrayPrices;
    }
}
