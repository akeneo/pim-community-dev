<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue;

/**
 * Boolean denormalizer used for the following attribute type:
 * - pim_catalog_boolean
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanDenormalizer extends AbstractValueDenormalizer
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return null === $data ? null : (bool) $data;
    }
}
