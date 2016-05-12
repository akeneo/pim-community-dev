<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Connector\ArrayConverter\Flat;

use Pim\Component\Connector\ArrayConverter\Flat\AttributeStandardConverter as BaseAttributeStandardConverter;

/**
 * Convert flat format to standard format for attribute
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AttributeStandardConverter extends BaseAttributeStandardConverter
{
    /**
     * {@inheritdoc}
     *
     * Converts flat csv array to standard structured array:
     */
    public function convert(array $item, array $options = [])
    {
        $convertedItem = parent::convert($item, $options);
        foreach ($item as $field => $data) {
            $booleanFields = [
                'localizable',
                'useable_as_grid_filter',
                'unique',
                'required',
                'scopable',
                'wysiwyg_enabled',
                'decimals_allowed',
                'negative_allowed',
                'is_read_only',
            ];

            $convertedItem = $this->convertFields($field, $booleanFields, $data, $convertedItem);
        }

        return $convertedItem;
    }
}
