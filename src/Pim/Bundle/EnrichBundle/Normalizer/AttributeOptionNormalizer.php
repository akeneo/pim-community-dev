<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;

/**
 * Attribute option normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    protected $serializer;

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $optionValues = $this->serializer->normalize($object->getOptionValues(), $format, $context);

        return [
            'id'            => $object->getId(),
            'code'          => $object->getCode(),
            'default'       => $object->isDefault(),
            'translatable'  => $object->isTranslatable(),
            'sort_order'    => $object->getSortOrder(),
            'option_values' => $optionValues
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeOption && $format === 'array';
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}
