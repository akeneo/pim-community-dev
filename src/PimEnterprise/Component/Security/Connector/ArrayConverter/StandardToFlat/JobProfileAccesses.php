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
 * Job Profile Accesses "Standard to Flat" format array converter
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class JobProfileAccesses implements ArrayConverterInterface
{
    /**
     * {@inheritdoc}
     *
     * Converts standard array to flat one:
     *
     * Before:
     * [
     *     [
     *         'job_profile'         => 'product_import',
     *         'user_group'          => 'IT support',
     *         'execute_job_profile' => true,
     *         'edit_job_profile'    => true,
     *     ],
     *     [
     *         'job_profile'         => 'product_import',
     *         'user_group'          => 'Manager',
     *         'execute_job_profile' => true,
     *         'edit_job_profile'    => false,
     *     ]
     * ]
     *
     * After:
     * [
     *      'job_profile'         => 'product_import',
     *      'execute_job_profile' => 'IT support,Manager',
     *      'edit_job_profile'    => 'IT support',
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $convertedItem = [
            'job_profile' => current($item)['job_profile']
        ];

        $executeItems = [];
        $editItems = [];

        foreach ($item as $groupPermission) {
            if (true === $groupPermission['execute_job_profile']) {
                $executeItems[] = $groupPermission['user_group'];
            }

            if (true === $groupPermission['edit_job_profile']) {
                $editItems[] = $groupPermission['user_group'];
            }
        }

        $convertedItem['execute_job_profile'] = implode(',', $executeItems);
        $convertedItem['edit_job_profile'] = implode(',', $editItems);

        return $convertedItem;
    }
}
