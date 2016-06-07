<?php

namespace Pim\Component\Connector\ArrayConverter\StandardToFlat;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;

/**
 * Standard to flat array converter for family
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Family implements ArrayConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert(array $item, array $options = [])
    {
        $convertedItem = [];

        foreach ($item as $field => $data) {
            $convertedItem = $this->convertField($field, $data, $convertedItem);
        }

        return $convertedItem;
    }

    /**
     * @param string $field
     * @param mixed  $data
     * @param array  $convertedItem
     *
     * @return array
     */
    protected function convertField($field, $data, array $convertedItem)
    {
        switch ($field) {
            case 'labels':
                foreach ($data as $localeCode => $label) {
                    $labelKey = sprintf('label-%s', $localeCode);
                    $convertedItem[$labelKey] = $label;
                }
                break;
            case 'requirements':
                foreach ($data as $scopeCode => $attributes) {
                    $requirementKey = sprintf('requirements-%s', $scopeCode);
                    $convertedItem[$requirementKey] = implode(',', $attributes);
                }
                break;
            case 'attributes':
                $convertedItem[$field] = implode(',', $data);
                break;
            default:
                $convertedItem[$field] = (string) $data;
        }

        return $convertedItem;
    }
}
