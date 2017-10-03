<?php

namespace Pim\Bundle\DataGridBundle\Normalizer;

use Pim\Component\Catalog\Model\GroupInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize family variant for the family variant grid
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FamilyVariantNormalizer extends ProductNormalizer
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
    public function normalize($familyVariant, $format = null, array $context = array())
    {
        $labels = $this->translationNormalizer->normalize($familyVariant, 'standard', $context);

        // We need to initialize the levels because of the grid.
        $normalizedFamilyVariant = [
            'id'                => $familyVariant->getId(),
            'familyCode'        => $familyVariant->getFamily()->getCode(),
            'familyVariantCode' => $familyVariant->getCode(),
            'label'             => isset($labels[$context['localeCode']]) ? $labels[$context['localeCode']] : $familyVariant->getCode(),
            'level_1'           => '',
            'level_2'           => ''
        ];

        foreach($familyVariant->getVariantAttributeSets() as $attributeSet) {
            $axesCodes = array_map(function ($attribute) {
                return $attribute->getLabel();
            }, $attributeSet->getAxes()->toArray());

            $normalizedFamilyVariant['level_' . $attributeSet->getLevel()] = implode($axesCodes, ', ');
        }

        return $normalizedFamilyVariant;
    }
}
