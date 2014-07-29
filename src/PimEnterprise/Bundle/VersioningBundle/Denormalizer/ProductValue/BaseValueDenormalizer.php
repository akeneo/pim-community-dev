<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer\ProductValue;

/**
 * Base value denormalizer used following attribute types:
 * - pim_catalog_identifier
 * - pim_catalog_number
 * - pim_catalog_boolean
 * - pim_catalog_text
 * - pim_catalog_textarea
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
