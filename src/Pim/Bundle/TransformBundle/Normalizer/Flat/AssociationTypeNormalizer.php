<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Flat;

use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\TransformBundle\Normalizer\Structured;

/**
 * Flat association type normalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeNormalizer extends Structured\AssociationTypeNormalizer
{
    /**
     * @var array $supportedFormats
     */
    protected $supportedFormats = array('csv');

    /**
     * {@inheritdoc}
     */
    protected function normalizeLabel(AssociationType $associationType)
    {
        $values = array();
        foreach ($associationType->getTranslations() as $translation) {
            $values[sprintf('label-%s', $translation->getLocale())] = $translation->getLabel();
        }

        return $values;
    }
}
