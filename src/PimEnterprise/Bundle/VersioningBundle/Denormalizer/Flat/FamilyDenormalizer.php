<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer\Flat;

/**
 * Family flat denormalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 */
class FamilyDenormalizer extends AbstractEntityDenormalizer
{
    /**
     * {@inheritdoc}
     */
    protected function doDenormalize($data, $format, array $context)
    {
        return $this->getEntity($data, $context);
    }
}
