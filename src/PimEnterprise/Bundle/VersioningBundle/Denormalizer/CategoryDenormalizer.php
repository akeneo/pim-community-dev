<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer;

/**
 * Category flat denormalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CategoryDenormalizer extends AbstractEntityDenormalizer
{
    /**
     * {@inheritdoc}
     */
    protected function doDenormalize($data, $format, array $context)
    {
        return $this->getEntity($data, $context);
    }
}
