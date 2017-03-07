<?php

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Normalizer\Standard\VariantGroupNormalizer as BaseNormalizer;

/**
 * A normalizer to transform a variant group entity into a flat array
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupNormalizer extends BaseNormalizer
{
    /** @var string[] */
    protected $supportedFormats = ['flat'];

    /**
     * {@inheritdoc}
     */
    public function normalize($variantGroup, $format = null, array $context = [])
    {
        $result = parent::normalize($variantGroup, $format, $context);

        $result['axis'] = implode(',', $result['axes']);
        unset($result['axes']);

        if (isset($result['values'])) {
            $result = $result + $result['values'];
            unset($result['values']);
        }

        $result += $this->normalizeLabels($result['labels']);
        unset($result['labels']);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeVariantGroupValues(GroupInterface $variantGroup)
    {
        if (null === $variantGroup->getProductTemplate()) {
            return [];
        }

        $values = $variantGroup->getProductTemplate()->getValues();

        $normalizedValues = [];
        foreach ($values as $value) {
            $normalizedValues = array_replace(
                $normalizedValues,
                $this->valuesNormalizer->normalize($value, 'flat', [])
            );
        }

        ksort($normalizedValues);

        return $normalizedValues;
    }

    /**
     * @param array $labels
     *
     * @return array
     */
    protected function normalizeLabels(array $labels)
    {
        $flatLabels = [];

        foreach ($labels as $code => $label) {
            $flatLabels['label-' . $code] = $label;
        }

        return $flatLabels;
    }
}
