<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Flat;

use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\TransformBundle\Normalizer\Structured;

/**
 * A normalizer to transform a group entity into a flat array
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupNormalizer extends Structured\GroupNormalizer
{
    /**
     * @var array $supportedFormats
     */
    protected $supportedFormats = array('csv');

    /**
     * {@inheritdoc}
     */
    protected function normalizeAxisAttributes(GroupInterface $group)
    {
        $attributes = parent::normalizeAxisAttributes($group);

        return implode(',', $attributes);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeVariantGroupValues(GroupInterface $group, $format, array $context)
    {
        if (!$group->getType()->isVariant() || null === $group->getProductTemplate()) {
            return [];
        }

        $valuesData = $group->getProductTemplate()->getValuesData();
        $values = $this->serializer->denormalize($valuesData, 'ProductValue[]', 'json');

        $normalizedValues = [];
        foreach ($values as $value) {
            $normalizedValues = array_merge(
                $normalizedValues,
                $this->serializer->normalize($value, $format, ['entity' => 'product'] + $context)
            );
        }

        ksort($normalizedValues);

        return $normalizedValues;
    }
}
