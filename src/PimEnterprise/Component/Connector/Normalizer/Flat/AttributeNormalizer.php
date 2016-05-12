<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Connector\Normalizer\Flat;

use Pim\Component\Connector\Normalizer\Flat\AttributeNormalizer as BaseAttributeNormalizer;

/**
 * A normalizer to transform an AttributeInterface entity into array
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AttributeNormalizer extends BaseAttributeNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return array_merge(
            parent::normalize($object, $format, $context),
            ['is_read_only' => (int) $object->isReadOnly()]
        );
    }
}
