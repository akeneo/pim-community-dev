<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Entity\AttributeOption;

/**
 * Flat attribute option normalizer
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatAttributeOptionNormalizer extends AttributeOptionNormalizer
{
    /**
     * @var array
     */
    protected $supportedFormats = array('csv');

    /**
     * {@inheritdoc}
     */
    protected function normalizeLabel(AttributeOption $entity)
    {
        $values = array();
        foreach ($entity->getOptionValues() as $translation) {
            $values[sprintf('label-%s', $translation->getLocale())] = $translation->getValue();
        }

        return $values;
    }
}
