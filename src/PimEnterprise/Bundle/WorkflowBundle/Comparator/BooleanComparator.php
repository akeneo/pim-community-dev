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
 * Comparator which calculate change set for booleans
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class BooleanComparator implements ComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsComparison($type)
    {
        return in_array($type, [
            'pim_catalog_boolean',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getChanges(array $data, array $originals)
    {
        if (!array_key_exists('value', $originals)) {
            return $data;
        }
        $castedNewValue = (bool) $data['value'];

        return $castedNewValue !== $originals['value'] ? $data : null;
    }
}
