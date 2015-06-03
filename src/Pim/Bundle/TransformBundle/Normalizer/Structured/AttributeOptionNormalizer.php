<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Structured;

use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
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
    protected $supportedFormats = array('json', 'xml');

    /**
     * {@inheritdoc}
     */
    public function normalize($entity, $format = null, array $context = array())
    {
        if (isset($context['entity']) && $context['entity'] === 'product') {
            return $entity->getCode();
        }

        return array(
            'attribute'  => $entity->getAttribute()->getCode(),
            'code'       => $entity->getCode(),
            'sort_order' => $entity->getSortOrder(),
        ) + $this->normalizeLabel($entity, $context);
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

        return array('label' => $labels);
    }
}
