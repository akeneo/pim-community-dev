<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\Normalizer;

use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize family variant for the family variant grid
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FamilyVariantNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
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
    public function normalize($familyVariant, $format = null, array $context = array()): array
    {
        $labels = $this->translationNormalizer->normalize($familyVariant, 'standard', $context);

        // We need to initialize the levels because of the grid.
        $normalizedFamilyVariant = [
            'id'                => $familyVariant->getId(),
            'familyCode'        => $familyVariant->getFamily()->getCode(),
            'familyVariantCode' => $familyVariant->getCode(),
            'label'             => isset($context['localeCode']) &&isset($labels[$context['localeCode']]) ?
                $labels[$context['localeCode']] :
                $familyVariant->getCode(),
            'level_1'           => '',
            'level_2'           => ''
        ];

        foreach ($familyVariant->getVariantAttributeSets() as $attributeSet) {
            $axesLabels = array_map(function ($attribute) {
                return $attribute->getLabel();
            }, $attributeSet->getAxes()->toArray());

            $normalizedFamilyVariant['level_' . $attributeSet->getLevel()] = implode($axesLabels, ', ');
        }

        return $normalizedFamilyVariant;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof FamilyVariantInterface && 'datagrid' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
