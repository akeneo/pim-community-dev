<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Entity\Association;

/**
 * Flat association normalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatAssociationNormalizer extends AssociationNormalizer
{
    /**
     * @var array
     */
    protected $supportedFormats = array('csv');

    /**
     * Normalize the label
     *
     * @param Association $association
     *
     * @return string
     */
    protected function normalizeLabel(Association $association)
    {
        $labels = parent::normalizeLabel($association);
        $flat = array();
        foreach ($labels as $locale => $label) {
            $flat[]= $locale.':'.$label;
        }

        return implode(', ', $flat);
    }
}
