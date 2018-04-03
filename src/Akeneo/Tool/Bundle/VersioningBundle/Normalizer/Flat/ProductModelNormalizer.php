<?php

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Pim\Component\Catalog\Model\ProductModelInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\scalar;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * A normalizer to transform a product model entity into a flat array
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelNormalizer extends SerializerAwareNormalizer implements NormalizerInterface
{
    /** @staticvar string */
    private const FIELD_CATEGORY = 'categories';

    /** @staticvar string */
    private const FIELD_FAMILY_VARIANT = 'family_variant';

    /** @staticvar string */
    private const FIELD_CODE = 'code';

    /** @staticvar string */
    private const ITEM_SEPARATOR = ',';

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array()): array
    {
        $context = $this->resolveContext($context);

        $results = [];
        $familyVariant = $object->getFamilyVariant();
        $results[self::FIELD_FAMILY_VARIANT] = null === $familyVariant ? null : $familyVariant->getCode();
        $results[self::FIELD_CODE] = $object->getCode();
        $results[self::FIELD_CATEGORY] = implode(self::ITEM_SEPARATOR, $object->getCategoryCodes());
        $results = array_replace($results, $this->normalizeValues($object, $format, $context));

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ProductModelInterface && in_array($format, ['flat']);
    }

    /**
     * Normalize values
     *
     * @param ProductModelInterface $productModel
     * @param string|null      $format
     * @param array            $context
     *
     * @return array
     */
    private function normalizeValues(ProductModelInterface $productModel, $format = null, array $context = []): array
    {
        $values = $productModel->getValuesForVariation();

        $normalizedValues = [];
        foreach ($values as $value) {
            $normalizedValues = array_replace(
                $normalizedValues,
                $this->serializer->normalize($value, $format, $context)
            );
        }
        ksort($normalizedValues);

        return $normalizedValues;
    }

    /**
     * Merge default format option with context
     *
     * @param array $context
     *
     * @return array
     */
    private function resolveContext(array $context): array
    {
        return array_merge(
            [
                'scopeCode'     => null,
                'localeCodes'   => [],
                'metric_format' => 'multiple_fields',
                'filter_types'  => ['pim.transform.product_value.flat']
            ],
            $context
        );
    }
}
