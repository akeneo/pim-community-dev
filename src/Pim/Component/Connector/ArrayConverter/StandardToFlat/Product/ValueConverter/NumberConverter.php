<?php

namespace Pim\Component\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

/**
 * Number array converter.
 * Convert a standard number array format to a flat one.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class NumberConverter extends AbstractValueConverter implements ValueConverterInterface
{
    /**
     * {@inheritdoc}
     *
     * Given a 'score' $attributeCode with this $data:
     * [
     *     [
     *         'locale' => null,
     *         'scope'  => null,
     *         'data'   => 19.9
     *     ],
     * ]
     *
     * It will return:
     * [
     *     'score' => 19.9,
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

            $convertedItem[$flatName] = floatval($value['data']);
        }

        return $convertedItem;
    }
}
