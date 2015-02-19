<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue;

/**
 * Base value denormalizer used following attribute types:
 * - pim_catalog_identifier
 * - pim_catalog_number
 * - pim_catalog_text
 * - pim_catalog_textarea
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseValueDenormalizer extends AbstractValueDenormalizer
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return '' === $data ? null : $data;
    }
}
