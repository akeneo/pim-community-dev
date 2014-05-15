<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Serialization;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Denormalize scalar product value
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ScalarProductValueDenormalizer implements DenormalizerInterface
{
    /** @var array */
    protected $scalarAttributeTypes;

    public function __construct()
    {
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
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if (
            !isset($context['instance']) ||
            !$context['instance'] instanceof AbstractProductValue
        ) {
            throw new \InvalidArgumentException(
                'A product value instance must be provided inside the context'
            );
        }

        return $context['instance']->setData($data);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return in_array($type, $this->scalarAttributeTypes);
    }
}
