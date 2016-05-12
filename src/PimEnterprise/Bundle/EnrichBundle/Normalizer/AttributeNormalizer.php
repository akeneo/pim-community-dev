<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Normalizer;

use Pim\Bundle\EnrichBundle\Normalizer\AttributeNormalizer as BaseAttributeNormalizer;
use Pim\Bundle\EnrichBundle\Provider\EmptyValue\EmptyValueProviderInterface;
use Pim\Bundle\EnrichBundle\Provider\Field\FieldProviderInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Attribute normalizer
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AttributeNormalizer extends BaseAttributeNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function normalize($attribute, $format = null, array $context = [])
    {
        return parent::normalize($attribute, $format, $context) + ['is_read_only' => $attribute->isReadOnly()];
    }
}
