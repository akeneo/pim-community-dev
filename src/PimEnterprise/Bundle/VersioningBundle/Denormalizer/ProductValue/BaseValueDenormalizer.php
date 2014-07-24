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

    /** @var array */
    protected $supportedFormats = array('csv');

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        // TODO: Implement denormalize() method.
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return in_array($type, $this->supportedTypes) && in_array($format, $this->supportedFormats);
}}
