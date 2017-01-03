<?php

namespace Pim\Component\Catalog\Normalizer\Structured;

use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Attribute option normalizer
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionNormalizer implements NormalizerInterface
{
    /**
     * @var array
     */
    protected $supportedFormats = ['json', 'xml'];

    /**
     * {@inheritdoc}
     */
    public function normalize($entity, $format = null, array $context = [])
    {
        if (isset($context['entity']) && in_array($context['entity'], ['product', 'variant-group'])) {
            return $entity->getCode();
        }

        return [
            'attribute'  => $entity->getAttribute()->getCode(),
            'code'       => $entity->getCode(),
            'sort_order' => $entity->getSortOrder(),
        ] + $this->normalizeLabel($entity, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeOptionInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Returns an array containing the label values
     *
     * @param AttributeOptionInterface $entity
     * @param array                    $context
     *
     * @return array
     */
    protected function normalizeLabel(AttributeOptionInterface $entity, $context)
    {
        $locales = isset($context['locales']) ? $context['locales'] : [];
        $labels = array_fill_keys($locales, '');
        foreach ($entity->getOptionValues() as $translation) {
            if (empty($locales) || in_array($translation->getLocale(), $locales)) {
                $labels[$translation->getLocale()] = $translation->getValue();
            }
        }

        return ['labels' => $labels];
    }
}
