<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Flat\ProductValue;

/**
 * DateTime flat denormalizer used for following attribute type:
 * - pim_catalog_date
 *
 * TODO: Should be used for \DateTime objects
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeDenormalizer extends AbstractValueDenormalizer
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if ($data === null || $data === '') {
            return null;
        }

        return new \DateTime(trim($data));
    }
}
