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
 * DateTime flat denormalizer used for following attribute type:
 * - pim_catalog_date
 *
 * TODO: Should be used for \DateTime objects
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class DateTimeDenormalizer extends AbstractValueDenormalizer
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        return new \DateTime(trim($data));
    }
}
