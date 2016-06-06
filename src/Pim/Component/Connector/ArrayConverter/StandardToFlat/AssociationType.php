<?php

namespace Pim\Component\Connector\ArrayConverter\StandardToFlat;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;

/**
 * Standard to flat array converter for association type
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AssociationType implements ArrayConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert(array $item, array $options = [])
    {
        $convertedItem = [];

        foreach ($item as $field => $data) {
            $convertedItem = $this->convertFields($field, $data, $convertedItem);
        }

        return $convertedItem;
    }

    /**
     * @param string $field
     * @param array  $data
     * @param array  $convertedItem
     *
     * @return array
     */
    protected function convertFields($field, $data, $convertedItem)
    {
        switch ($field) {
            case 'labels':
                foreach ($data as $localeCode => $label) {
                    $labelKey = sprintf('label-%s', $localeCode);
                    $convertedItem[$labelKey] = $label;
                }
                break;
            default:
                $convertedItem[$field] = (string) $data;
        }

        return $convertedItem;
    }
}
