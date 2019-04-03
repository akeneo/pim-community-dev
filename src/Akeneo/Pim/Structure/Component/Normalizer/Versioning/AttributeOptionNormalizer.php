<?php

namespace Akeneo\Pim\Structure\Component\Normalizer\Versioning;

use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Normalizer\Standard\AttributeOptionNormalizer as BaseNormalizer;

/**
 * Normalize an attribute option
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionNormalizer extends BaseNormalizer
{
    /** @var string[] */
    protected $supportedFormats = ['flat'];

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

        $attributeOption = $this->normalizeAttributeOption($object, $format, $context);
        unset($attributeOption['labels']);
        $attributeOption += $this->normalizeLabels($object, $context);

        return $attributeOption;
    }

    private function normalizeAttributeOption($object, ?string $format, array $context): array
    {
        return parent::normalize($object, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeLabels(AttributeOptionInterface $entity, $context): array
    {
        $labels = [];
        $locales = isset($context['locales']) ? $context['locales'] : [];
        foreach ($locales as $locale) {
            $labels[sprintf('label-%s', $locale)] = '';
        }

        foreach ($entity->getOptionValues() as $translation) {
            if (empty($locales) || in_array($translation->getLocale(), $locales)) {
                $labels[sprintf('label-%s', $translation->getLocale())] = $translation->getValue();
            }
        }

        return $labels;
    }
}
