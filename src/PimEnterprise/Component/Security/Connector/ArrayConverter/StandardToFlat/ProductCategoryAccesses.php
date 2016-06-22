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
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class ProductCategoryAccesses implements ArrayConverterInterface
{
    /**
     * {@inheritdoc}
     *
     * Converts standard array to flat one:
     *
     * Before:
     * [
     *     [
     *         'category'   => '2013_collection',
     *         'user_group' => 'IT support',
     *         'view_items' => true,
     *         'edit_items' => true,
     *         'own_items'  => false,
     *     ],
     *     [
     *         'category'   => '2013_collection',
     *         'user_group' => 'Manager',
     *         'view_items' => true,
     *         'edit_items' => false,
     *         'own_items'  => false,
     *     ]
     * ]
     *
     * After:
     * [
     *      'category'   => '2013_collection',
     *      'view_items' => 'IT support,Manager',
     *      'edit_items' => 'IT support',
     *      'own_items'  => '',
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $convertedItem = [
            'category' => current($item)['category']
        ];

        $viewItems = [];
        $editItems = [];
        $ownItems  = [];

        foreach ($item as $groupPermission) {
            if (true === $groupPermission['view_items']) {
                $viewItems[] = $groupPermission['user_group'];
            }

            if (true === $groupPermission['edit_items']) {
                $editItems[] = $groupPermission['user_group'];
            }

            if (true === $groupPermission['own_items']) {
                $ownItems[] = $groupPermission['user_group'];
            }
        }

        $convertedItem['view_items'] = implode(',', $viewItems);
        $convertedItem['edit_items'] = implode(',', $editItems);
        $convertedItem['own_items']  = implode(',', $ownItems);

        return $convertedItem;
    }
}
