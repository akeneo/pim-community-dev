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
    public function supportsComparison($type)
    {
        return in_array($type, ['pim_catalog_multiselect', 'pim_reference_data_multiselect']);
    }

    /**
     * {@inheritdoc}
     */
    public function getChanges(array $data, array $originals)
    {
        $codes = [];
        foreach ($data['value'] as $index => $attribute) {
            if (!array_key_exists('value', $originals)
                || !isset($originals['value'][$index])
                || $attribute['code'] !== $originals['value'][$index]['code']
            ) {
                $codes[] = $attribute['code'];
            }
        }

        if (empty($codes)) {
            return null;
        }

        return [
            'locale' => $data['locale'],
            'scope'  => $data['scope'],
            'value'  => $codes,
        ];
    }
}
