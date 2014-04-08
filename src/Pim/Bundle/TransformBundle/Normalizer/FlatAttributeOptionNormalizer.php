<?php

namespace Pim\Bundle\TransformBundle\Normalizer;

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
     * @var array $supportedFormats
     */
    protected $supportedFormats = array('csv');

    /**
     * {@inheritdoc}
     */
    protected function normalizeLabel(AttributeOption $entity, $context)
    {
        $labels = array();
        foreach ($context['locales'] as $locale) {
            $labels[sprintf('label-%s', $locale)] = '';
        }

        foreach ($entity->getOptionValues() as $translation) {
            $labels[sprintf('label-%s', $translation->getLocale())] = $translation->getValue();
        }

        return $labels;
    }
}
