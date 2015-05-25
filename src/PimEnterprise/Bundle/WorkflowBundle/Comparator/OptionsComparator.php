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
 * Comparator which calculate change set for collections of options
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class OptionsComparator implements ComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsComparison($attributeType)
    {
        return in_array($attributeType, ['pim_catalog_multiselect', 'pim_reference_data_multiselect']);
    }

    /**
     * {@inheritdoc}
     */
    public function getChanges(array $changes, array $originals)
    {
        $codes = [];
        foreach ($changes['value'] as $index => $attribute) {
            if (!array_key_exists('value', $originals)
                || !isset($originals['value'][$index])
                || $attribute['code'] !== $originals['value'][$index]['code']) {
                $codes[] = $attribute['code'];
            }
        }

        if (empty($codes)) {
            return null;
        }

        return [
            'locale' => $changes['locale'],
            'scope'  => $changes['scope'],
            'value'  => $codes,
        ];
    }
}
