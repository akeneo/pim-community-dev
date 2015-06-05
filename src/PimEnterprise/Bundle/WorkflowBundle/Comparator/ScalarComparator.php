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
 * Comparator which calculate change set for scalars
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class ScalarComparator implements ComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsComparison($type)
    {
        return in_array($type, [
            'pim_catalog_date',
            'pim_catalog_identifier',
            'pim_catalog_number',
            'pim_catalog_text',
            'pim_catalog_textarea'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getChanges(array $data, array $originals)
    {
        $default = ['locale' => null, 'scope' => null, 'value' => null];
        $originals = array_merge($default, $originals);

        return $data['value'] !== $originals['value'] ? $data : null;
    }
}
