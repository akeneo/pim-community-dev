<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Serialization;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Denormalize flat product
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class FlatProductValueDenormalizer implements DenormalizerInterface, SerializerAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if (!isset($context['instance']) || !$context['instance'] instanceof AbstractProductValue) {
            throw new \InvalidArgumentException('A product value instance must be provided inside the context');
        }

        $value = $context['instance'];
        switch ($value->getAttribute()->getAttributeType()) {
            case 'pim_catalog_identifier':
            case 'pim_catalog_text':
            case 'pim_catalog_textarea':
            case 'pim_catalog_number':
                $value->setData($data);
                break;

            default:
                throw new \Exception('Not implemented yet');
                $value->setData(
                    $this->denormalizer->denormalize($data, $class, $format, ['data' => $context['instance']])
                );
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        $refClass = new \ReflectionClass($type);

        return $refClass->isSubclassOf('Pim\Bundle\CatalogBundle\Model\AbstractProductValue') && 'csv' === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        if (!$serializer instanceof DenormalizerInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Class "%s" is expecting a denormalizer',
                    get_class($this)
                )
            );
        }
        $this->serializer = $serializer;
    }
}
