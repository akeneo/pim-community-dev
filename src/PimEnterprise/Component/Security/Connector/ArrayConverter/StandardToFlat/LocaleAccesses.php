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
 * Locale Accesses "Standard to Flat" format array converter
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class LocaleAccesses implements ArrayConverterInterface
{
    /**
     * {@inheritdoc}
     *
     * Converts standard array to flat one:
     *
     * Before:
     * [
     *     [
     *         'locale'        => 'en_US',
     *         'user_group'    => 'IT support',
     *         'view_products' => true,
     *         'edit_products' => true,
     *     ],
     *     [
     *         'locale'        => 'en_US',
     *         'user_group'    => 'Manager',
     *         'view_products' => true,
     *         'edit_products' => false,
     *     ]
     * ]
     *
     * After:
     * [
     *      'locale'        => 'en_US',
     *      'view_products' => 'IT support,Manager',
     *      'edit_products' => 'IT support',
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $convertedItem = [
            'locale' => current($item)['locale']
        ];

        $viewItems = [];
        $editItems = [];

        foreach ($item as $groupPermission) {
            if (true === $groupPermission['view_products']) {
                $viewItems[] = $groupPermission['user_group'];
            }

            if (true === $groupPermission['edit_products']) {
                $editItems[] = $groupPermission['user_group'];
            }
        }

        $convertedItem['view_products'] = implode(',', $viewItems);
        $convertedItem['edit_products'] = implode(',', $editItems);

        return $convertedItem;
    }
}
