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
class ScalarComparator implements AttributeComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsComparison($attributeType)
    {
        return in_array($attributeType, [
            'pim_catalog_boolean',
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
    public function getChanges(array $changes, array $originals)
    {
        return !array_key_exists('value', $originals) || $changes['value'] !== $originals['value'] ? $changes : null;
    }
}
