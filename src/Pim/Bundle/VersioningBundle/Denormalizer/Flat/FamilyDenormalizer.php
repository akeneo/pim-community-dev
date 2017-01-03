<?php

namespace Pim\Bundle\VersioningBundle\Denormalizer\Flat;

/**
 * Family flat denormalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyDenormalizer extends AbstractEntityDenormalizer
{
    /**
     * {@inheritdoc}
     */
    protected function doDenormalize($data, $format, array $context)
    {
        return $this->getObject($data, $context);
    }
}
