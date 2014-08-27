<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Abstract value flat denormalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 */
abstract class AbstractValueDenormalizer implements DenormalizerInterface
{
    /** @var string[] */
    protected $supportedFormats = array('csv');

    /** @var string[] */
    protected $supportedTypes;

    /**
     * @param string[] $supportedTypes
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
