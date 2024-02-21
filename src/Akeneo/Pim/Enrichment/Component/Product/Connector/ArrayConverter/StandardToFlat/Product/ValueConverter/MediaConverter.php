<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

/**
 * Media array converter.
 * Convert a standard media array format to a flat one.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MediaConverter extends AbstractValueConverter implements ValueConverterInterface
{
    /**
     * {@inheritdoc}
     *
     * Convert a standard formatted media product value to a flat one.
     *
     * Given a 'front_picture' $attributeCode with this $data:
     * [
     *     [
     *         'locale' => 'de_DE',
     *         'scope'  => 'ecommerce',
     *         'data'   => 'x5/78/87/sdqdsqf654qsd6f5465sdqfsqdf65_toto.jpg'
     *     ],
     * ]
     *
     * It will return:
     * [
     *     'front_picture-de_DE-ecommerce' => 'x5/78/87/sdqdsqf654qsd6f5465sdqfsqdf65_toto.jpg',
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

            $convertedItem[$flatName] = (string) $value['data'];
        }

        return $convertedItem;
    }
}
