<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Structured;

use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
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
     * @var array $supportedFormats
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
            'default'    => ($entity->isDefault()) ? '1' : '0',
        ) + $this->normalizeLabel($entity, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeOption && in_array($format, $this->supportedFormats);
    }

    /**
     * Returns an array containing the label values
     *
     * @param AttributeOption $entity
     * @param array           $context
     *
     * @return array
     */
    protected function normalizeLabel(AttributeOption $entity, $context)
    {
        $labels = array_fill_keys($context['locales'], '');
        foreach ($entity->getOptionValues() as $translation) {
            $labels[$translation->getLocale()] = $translation->getValue();
        }

        return array('label' => $labels);
    }
}
