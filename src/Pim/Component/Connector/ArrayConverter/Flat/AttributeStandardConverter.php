<?php

namespace Pim\Component\Connector\ArrayConverter\Flat;

use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;

/**
 * Convert flat format to standard format for attribute
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeStandardConverter implements StandardArrayConverterInterface
{
    /**
     * {@inheritdoc}
     *
     * Converts flat csv array to standard structured array:
     */
    public function convert(array $item, array $options = [])
    {
        $convertedItem = ['labels' => []];
        foreach ($item as $field => $data) {
            $fields = ['localizable', 'useable_as_grid_filter', 'unique', 'scopable'];
            if (in_array($field, $fields)) {
                $data = (bool) $data;
            }

            $isLabel = false !== strpos($field, 'label-', 0);
            if ($isLabel) {
                $labelTokens = explode('-', $field);
                $labelLocale = $labelTokens[1];
                $convertedItem['labels'][$labelLocale] = $data;
            } elseif ('type' === $field) {
                $convertedItem['attributeType'] = $data;
            } else {
                $convertedItem[$field] = $data;
            }
        }

        return $convertedItem;
    }
}
