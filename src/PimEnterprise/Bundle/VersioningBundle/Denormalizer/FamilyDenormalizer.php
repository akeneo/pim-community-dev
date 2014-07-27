<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class FamilyDenormalizer extends AbstractEntityDenormalizer
{
    /**
     * {@inheritdoc}
     */
    protected function doDenormalize(array $data, $format, array $context)
    {
        return $this->getEntity($data, $context);
    }
}
