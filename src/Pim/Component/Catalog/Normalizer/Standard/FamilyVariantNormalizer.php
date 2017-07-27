<?php

namespace Pim\Component\Catalog\Normalizer\Standard;

use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FamilyVariantNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    private $translationNormalizer;

    /**
     * @param NormalizerInterface $translationNormalizer
     */
    public function __construct(
        NormalizerInterface $translationNormalizer
    ) {
        $this->translationNormalizer = $translationNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($familyVariant, $format = null, array $context = []): array
    {
        return [
            'code' => $familyVariant->getCode(),
            'labels' => $this->translationNormalizer->normalize($familyVariant, 'standard', $context),
            'family' => $familyVariant->getFamily()->getCode(),
            'variant_attribute_sets' => $this->normalizeVariantAttributeSets($familyVariant->getVariantAttributeSets()),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof FamilyVariantInterface && 'standard' === $format;
    }

    /**
     * Normalizes a collection of variant attribute sets.
     *
     * It returns the following:
     *
     * [
     *     [
     *         "level" => 1,
     *         "axes" => [
     *             "a_simple_select"
     *         ],
     *         "attributes" => [
     *             "an_attribute",
     *             "an_other_attribute"
     *         ],
     *     ],
     * ]
     *
     * @param Collection $variantAttributeSets
     *
     * @return array
     */
    private function normalizeVariantAttributeSets(Collection $variantAttributeSets): array
    {
        return $variantAttributeSets->map(function (VariantAttributeSetInterface $variantAttributeSet) {
            return [
                'level' => $variantAttributeSet->getLevel(),
                'axes' => $this->normalizeAttributes($variantAttributeSet->getAxes()),
                'attributes' => $this->normalizeAttributes($variantAttributeSet->getAttributes()),
            ];
        })->toArray();
    }

    /**
     * Normalizes a collection of attributes as an array of attribute codes.
     *
     * @param Collection $attributes
     *
     * @return array
     */
    private function normalizeAttributes(Collection $attributes): array
    {
        return $attributes->map(function (AttributeInterface $attribute) {
            return $attribute->getCode();
        })->toArray();
    }
}
