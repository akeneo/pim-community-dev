<?php

namespace Pim\Component\Connector\Normalizer;

use Pim\Component\Catalog\Normalizer\GroupNormalizer as BaseNormalizer;
use Pim\Component\Catalog\Model\GroupInterface;

/**
 * A normalizer to transform a group entity into a flat array
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupNormalizer extends BaseNormalizer
{
    /** @var string[] */
    protected $supportedFormats = ['csv'];

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $result = parent::normalize($object, $format, $context);

        if (isset($result['values'])) {
            $result = $result + $result['values'];
            unset($result['values']);
        }

        return $result;
    }

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
        $values = $this->valuesDenormalizer->denormalize($valuesData, 'ProductValue[]', 'json');

        $normalizedValues = [];
        foreach ($values as $value) {
            $normalizedValues = array_replace(
                $normalizedValues,
                $this->serializer->normalize($value, $format, ['entity' => 'product'] + $context)
            );
        }

        ksort($normalizedValues);

        return $normalizedValues;
    }
}
