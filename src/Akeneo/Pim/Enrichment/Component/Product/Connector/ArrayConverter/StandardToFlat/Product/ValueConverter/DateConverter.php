<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DateConverter extends AbstractValueConverter implements ValueConverterInterface
{
    /**
     * {@inheritdoc}
     *
     * Given a 'release_date' $attributeCode with this $data:
     * [
     *     [
     *         'locale' => null,
     *         'scope'  => null,
     *         'data'   => '2005-08-15',
     *     ],
     * ]
     *
     * It will return:
     * [
     *     'release_date' => '2005-08-15',
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

            // TODO: Check if we have a timezoned date as input
            // $date = \DateTime::createFromFormat(\DateTime::ATOM, $value['data']);
            // $convertedItem[$flatName] = $date->format('Y-m-d');

            $convertedItem[$flatName] = $value['data'];
        }

        return $convertedItem;
    }
}
