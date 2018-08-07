<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

/**
 * Price array converter.
 * Convert a standard price array format to a flat one.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PriceConverter extends AbstractValueConverter implements ValueConverterInterface
{
    /**
     * {@inheritdoc}
     *
     * Convert a standard formatted price product value to a flat one.
     *
     * Given a 'super_price' $attributeCode with this $data:
     * [
     *     [
     *         'locale' => 'de_DE',
     *         'scope'  => 'ecommerce',
     *         'data'   => [
     *             [
     *                 'amount'   => '10',
     *                 'currency' => 'EUR'
     *             ],
     *             [
     *                 'amount'   => '9',
     *                 'currency' => 'USD'
     *             ],
     *         ]
     *     ],
     *     [
     *         'locale' => 'fr_FR',
     *         'scope'  => 'ecommerce',
     *         'data'   => [
     *             [
     *                 'amount'   => '30',
     *                 'currency' => 'EUR'
     *             ],
     *             [
     *                 'amount'   => '29',
     *                 'currency' => 'USD'
     *             ],
     *         ]
     *     ]
     * ]
     *
     * It will return:
     * [
     *     'super_price-de_DE-ecommerce-EUR' => '10',
     *     'super_price-de_DE-ecommerce-USD' => '9',
     *     'super_price-fr_FR-ecommerce-EUR' => '30',
     *     'super_price-fr_FR-ecommerce-USD' => '29',
     * ]
     */
    public function convert($attributeCode, $data)
    {
        $convertedItem = [];

        foreach ($data as $value) {
            $flatName = $this->columnsResolver->resolveFlatAttributeName(
                $attributeCode,
                $value['locale'],
                $value['scope']
            );

            foreach ($value['data'] as $currency) {
                $flatCurrencyName = sprintf('%s-%s', $flatName, $currency['currency']);
                $convertedItem[$flatCurrencyName] = (string) $currency['amount'];
            }
        }

        return $convertedItem;
    }
}
