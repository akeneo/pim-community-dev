<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\File\Converter;

/**
 * Attribute Option CSV Converter
 *
 * @author    Nicolas Dupont <nicola@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvAttributeOptionToStandardConverter implements StandardFormatConverterInterface
{
    /**
     * Converts flat csv array to standard structured array:
     *
     * Before:
     * {
     *     'attribute': 'maximum_print_size',
     *     'code': '210_x_1219_mm',
     *     'sort_order': 2,
     *     'label-de_DE': '210 x 1219 mm',
     *     'label-en_US': '210 x 1219 mm',
     *     'label-fr_FR': '210 x 1219 mm'
     * }
     *
     * After:
     * {
     *     'attribute': 'maximum_print_size',
     *     'code': '210_x_1219_mm',
     *     'sort_order': 2,
     *     'labels': {
     *         'de_DE': '210 x 1219 mm',
     *         'en_US': '210 x 1219 mm',
     *         'fr_FR': '210 x 1219 mm'
     *     }
     * }
     *
     * @param array $item Representing a flat attribute option
     *
     * @return array structured product
     */
    public function convert($item)
    {
        // TODO: option resolver!

        $convertedItem = ['labels' => []];
        foreach ($item as $field => $data) {
            $isLabel = false !== strpos($field, 'label-', 0);
            if ($isLabel) {
                $labelTokens = explode('-', $field);
                $labelLocale = $labelTokens[1];
                $convertedItem['labels'][$labelLocale] = $data;
            } else {
                $convertedItem[$field] = $data;
            }
        }

        return $convertedItem;
    }
}
