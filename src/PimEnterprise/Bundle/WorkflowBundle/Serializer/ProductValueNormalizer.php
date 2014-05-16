<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

/**
 * Denormalize scalar product value
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductValueNormalizer extends SerializerAwareNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /** @staticvar string */
    const FORMAT = 'proposal';

    /** @var array */
    protected $scalarAttributeTypes;

    /**
     * Constructor
     */
    public function __construct()
    {
        // TODO (2014-05-15 15:07 by Gildas): Is it interesting to configure scalar attribute types?
        $this->scalarAttributeTypes = [
            'pim_catalog_identifier',
            'pim_catalog_text',
            'pim_catalog_textarea',
            'pim_catalog_number',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        // TODO (2014-05-15 15:07 by Gildas): data must be an instance of AbstractProductValue
        $data = $object->getData();

        if (!is_scalar($data)) {
            $data = $this->serializer->normalize($data, $format, $context);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        // TODO (2014-05-15 15:08 by Gildas): $context['instance'] must be an instance of AbstractProductValue
        $instance = $context['instance'];
        // TODO (2014-05-16 11:52 by Gildas): ensure attribute_type key presence
        $attributeType = $context['attribute_type'];

        if (!in_array($attributeType, $this->scalarAttributeTypes)) {
            $data = $this->serializer->denormalize(
                $data,
                $attributeType,
                $format,
                ['instance' => $instance->getData()]
            );
        }

        return $instance->setData($data);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AbstractProductValue && self::FORMAT === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return self::FORMAT === $format && 'value' === $type;
    }
}
