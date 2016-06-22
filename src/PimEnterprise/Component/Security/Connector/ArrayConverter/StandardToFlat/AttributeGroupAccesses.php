<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Security\Connector\ArrayConverter\StandardToFlat;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;

/**
 * Attribute Group Accesses "Standard to Flat" format array converter
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class AttributeGroupAccesses implements ArrayConverterInterface
{
    /**
     * {@inheritdoc}
     *
     * Converts standard array to flat one:
     *
     * Before:
     * [
     *     [
     *         'attribute_group' => 'other',
     *         'user_group'      => 'IT support',
     *         'view_attributes' => true,
     *         'edit_attributes' => true,
     *     ], [
     *         'attribute_group' => 'other',
     *         'user_group'      => 'Manager',
     *         'view_attributes' => true,
     *         'edit_attributes' => false,
     *     ]
     * ]
     *
     * After:
     * [
     *      'attribute_group' => 'other',
     *      'view_attributes' => 'IT support,Manager',
     *      'edit_attributes' => 'IT support',
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $convertedItem = [
            'attribute_group' => current($item)['attribute_group']
        ];

        $viewItems = [];
        $editItems = [];

        foreach ($item as $groupPermission) {
            if (true === $groupPermission['view_attributes']) {
                $viewItems[] = $groupPermission['user_group'];
            }

            if (true === $groupPermission['edit_attributes']) {
                $editItems[] = $groupPermission['user_group'];
            }
        }

        $convertedItem['view_attributes'] = implode(',', $viewItems);
        $convertedItem['edit_attributes'] = implode(',', $editItems);

        return $convertedItem;
    }
}
