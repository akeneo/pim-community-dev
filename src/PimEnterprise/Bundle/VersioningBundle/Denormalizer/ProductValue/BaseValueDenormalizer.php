<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer\ProductValue;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class BaseValueDenormalizer extends AbstractValueDenormalizer
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        return $data;
    }
}
