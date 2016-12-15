<?php

namespace Pim\Component\Catalog\Normalizer\Standard;

use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($attributeOption, $format = null, array $context = [])
    {
        return [
            'code'       => $attributeOption->getCode(),
            'attribute'  => null === $attributeOption->getAttribute() ?
                null : $attributeOption->getAttribute()->getCode(),
            'sort_order' => (int) $attributeOption->getSortOrder(),
            'labels'     => $this->normalizeLabels($attributeOption, $context),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeOptionInterface && 'standard' === $format;
    }

    /**
     * Returns an array containing the label values
     *
     * @param AttributeOptionInterface $attributeOption
     * @param array                    $context
     *
     * @return array
     */
    protected function normalizeLabels(AttributeOptionInterface $attributeOption, $context)
    {
        $locales = isset($context['locales']) ? $context['locales'] : [];
        $labels = array_fill_keys($locales, null);

        foreach ($attributeOption->getOptionValues() as $translation) {
            if (empty($locales) || in_array($translation->getLocale(), $locales)) {
                $labels[$translation->getLocale()] = $translation->getValue();
            }
        }

        return $labels;
    }
}
