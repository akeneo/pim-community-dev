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
class OptionComparator implements AttributeComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsComparison($attributeType)
    {
        return in_array($attributeType, ['pim_catalog_simpleselect', 'pim_reference_data_simpleselect']);
    }

    /**
     * {@inheritdoc}
     */
    public function getChanges(array $changes, array $originals)
    {
        if (!array_key_exists('value', $originals) || $changes['value']['code'] !== $originals['value']['code']) {
            return [
                'locale' => $changes['locale'],
                'scope'  => $changes['scope'],
                'value'  => $changes['value']['code'],
            ];
        }
    }
}
