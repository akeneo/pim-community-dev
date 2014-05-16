<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Serialization;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

/**
 * Denormalize flat product
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class FlatProductValueDenormalizer implements DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if (!isset($context['instance']) || !$context['instance'] instanceof AbstractProductValue) {
            throw new \InvalidArgumentException('A product value instance must be provided inside the context');
        }

        $instance = $context['instance'];
        if (is_scalar($instance->getData())) {
            $instance->setData($data);
        }

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return 'Pim\Bundle\CatalogBundle\Model\AbstractProductValue' === $type && 'csv' === $format;
    }
}
