<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer\ProductValue;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
abstract class AbstractValueDenormalizer implements DenormalizerInterface
{
    /** @var string[] */
    protected $supportedFormats = array('csv');

    /** @var string[] */
    protected $supportedTypes;

    /**
     * @param string $supportedTypes
     */
    public function __construct(array $supportedTypes)
    {
        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return in_array($type, $this->supportedTypes) && in_array($format, $this->supportedFormats);
    }
}
