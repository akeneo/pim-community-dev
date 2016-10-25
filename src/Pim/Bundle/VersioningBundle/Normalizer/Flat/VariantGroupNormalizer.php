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

        $valuesData = $variantGroup->getProductTemplate()->getValuesData();
        $values = $this->valuesDenormalizer->denormalize($valuesData, 'ProductValue[]', 'json');

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
     * Generate an array representing the list of variant group values in flat array
     *
     * @param array $variantGroupValues
     * @param array $context
     *
     * @return array
     */
    protected function normalizeValues(array $variantGroupValues, array $context = [])
    {
        $flatValues = [];

        foreach ($variantGroupValues as $attributeCode => $variantGroupValue) {
            $flatValues += $this->valuesNormalizer->normalize(
                [$attributeCode => $variantGroupValue],
                'flat',
                $context
            );
        }

        return $flatValues;
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
