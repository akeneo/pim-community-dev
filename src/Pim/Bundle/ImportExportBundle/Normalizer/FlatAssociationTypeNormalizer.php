<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Entity\AssociationType;

/**
 * Flat association type normalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatAssociationTypeNormalizer extends AssociationTypeNormalizer
{
    /**
     * @var array
     */
    protected $supportedFormats = ['csv'];

    /**
     * {@inheritdoc}
     */
    protected function normalizeLabel(AssociationType $associationType)
    {
        $values = [];
        foreach ($associationType->getTranslations() as $translation) {
            $values[sprintf('label-%s', $translation->getLocale())] = $translation->getLabel();
        }

        return $values;
    }
}
