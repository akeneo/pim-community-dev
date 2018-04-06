<?php

namespace Pim\Component\Connector\ArrayConverter\StandardToFlat;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;

/**
 * Convert standard format to flat format for channel
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Channel extends AbstractSimpleArrayConverter implements ArrayConverterInterface
{
    /**
     * {@inheritdoc}
     */
    protected function convertProperty($property, $data, array $convertedItem, array $options)
    {
        switch ($property) {
            case 'locales':
            case 'currencies':
                $convertedItem[$property] = implode(',', array_filter($data));
                break;
            case 'conversion_units':
                $formattedConvertedUnits = array_map(function ($key) use ($data) {
                    return sprintf('%s:%s', trim($key), trim($data[$key]));
                }, array_keys(array_filter($data)));

                $convertedItem[$property] = implode(',', $formattedConvertedUnits);
                break;
            case 'category_tree':
                $convertedItem['tree'] = (string) $data;
                break;
            case 'labels':
                foreach ($data as $localeCode => $label) {
                    $labelKey = sprintf('label-%s', $localeCode);
                    $convertedItem[$labelKey] = $label;
                }
                break;
            default:
                $convertedItem[$property] = (string) $data;
        }

        return $convertedItem;
    }
}
