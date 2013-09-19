<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;

/**
 * Flat attribute group normalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatAttributeGroupNormalizer extends AttributeGroupNormalizer
{
    /**
     * @var string[]
     */
    protected $supportedFormats = array('csv');

    /**
     * @var array
     */
    protected $results;

    /**
     * {@inheritdoc}
     */
    protected function normalizeName(AttributeGroup $group)
    {
        $names = parent::normalizeName($group);
        $flat = array();
        foreach ($names as $locale => $name) {
            $flat[]= $locale.':'.$name;
        }

        return implode(', ', $flat);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeAttributes(AttributeGroup $group)
    {
        $attributes = parent::normalizeAttributes($group);

        return implode(', ', $attributes);
    }
}
