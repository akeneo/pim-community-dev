<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Comparator;

/**
 * Comparator which calculate change set for options
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class OptionComparator implements ComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsComparison($type)
    {
        return in_array($type, ['pim_catalog_simpleselect', 'pim_reference_data_simpleselect']);
    }

    /**
     * {@inheritdoc}
     */
    public function getChanges(array $data, array $originals)
    {
        $default = ['locale' => null, 'scope' => null, 'value' => null];
        $originals = array_merge($default, $originals);

        if ($data['value']['code'] === $originals['value']['code']) {
            return null;
        }

        return [
            'locale' => $data['locale'],
            'scope'  => $data['scope'],
            'value'  => $data['value']['code'],
        ];
    }
}
