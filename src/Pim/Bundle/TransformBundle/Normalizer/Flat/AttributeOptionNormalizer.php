<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Flat;

use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\TransformBundle\Normalizer\Structured;

/**
 * Normalize an attribute option
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @see       Pim\Bundle\TransformBundle\Normalizer\Flat\ProductNormalizer
 */
class AttributeOptionNormalizer extends Structured\AttributeOptionNormalizer
{
    /** @var array */
    protected $supportedFormats = array('csv', 'flat');

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeOptionInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (array_key_exists('field_name', $context)) {
            return [
                $context['field_name'] => $object->getCode(),
            ];
        }

        return parent::normalize($object, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeLabel(AttributeOptionInterface $entity, $context)
    {
        $labels = array();
        foreach ($context['locales'] as $locale) {
            $labels[sprintf('label-%s', $locale)] = '';
        }

        foreach ($entity->getOptionValues() as $translation) {
            if (empty($context['locales']) || in_array($translation->getLocale(), $context['locales'])) {
                $labels[sprintf('label-%s', $translation->getLocale())] = $translation->getValue();
            }
        }

        return $labels;
    }
}
