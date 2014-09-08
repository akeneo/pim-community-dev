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

/**
 * Base value denormalizer used following attribute types:
 * - pim_catalog_identifier
 * - pim_catalog_number
 * - pim_catalog_boolean
 * - pim_catalog_text
 * - pim_catalog_textarea
 *
 * @author Romain Monceau <romain@akeneo.com>
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
