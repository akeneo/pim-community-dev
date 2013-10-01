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
    protected function normalizeLabel(AttributeGroup $group)
    {
        $labels = parent::normalizeLabel($group);
        $flat = array();
        foreach ($labels as $locale => $label) {
            $flat[]= $locale.':'.$label;
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
