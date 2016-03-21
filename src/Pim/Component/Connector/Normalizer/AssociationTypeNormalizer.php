<?php

namespace Pim\Component\Connector\Normalizer;

use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Normalizer\AssociationTypeNormalizer as BaseNormalizer;

/**
 * Flat association type normalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeNormalizer extends BaseNormalizer
{
    /** @var string[] */
    protected $supportedFormats = ['csv'];

    /**
     * {@inheritdoc}
     */
    protected function normalizeLabel(AssociationTypeInterface $associationType)
    {
        $values = [];
        foreach ($associationType->getTranslations() as $translation) {
            $values[sprintf('label-%s', $translation->getLocale())] = $translation->getLabel();
        }

        return $values;
    }
}
