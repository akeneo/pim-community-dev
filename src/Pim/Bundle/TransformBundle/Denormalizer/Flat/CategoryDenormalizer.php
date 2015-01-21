<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Flat;

/**
 * Category flat denormalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
