<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer\ProductValue;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class BaseValueDenormalizer implements DenormalizerInterface
{

    /** @var array */
    protected $supportedTypes = array(
        'pim_catalog_identifier',
        'pim_catalog_number',
        'pim_catalog_text',
        'pim_catalog_textarea',
        'pim_catalog_date'
    );

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $value = $context['entity'];
        $value->setData($data);

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return in_array($type, $this->supportedTypes) && 'csv' === $format;
    }
}
