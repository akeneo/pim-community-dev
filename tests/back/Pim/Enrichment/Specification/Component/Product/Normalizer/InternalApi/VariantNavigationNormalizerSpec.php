<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class VariantNavigationNormalizerSpec extends ObjectBehavior
{
    function let(
        LocaleRepositoryInterface $localeRepository,
        NormalizerInterface $entityWithFamilyVariantNormalizer
    ) {
        $localeRepository->getActivatedLocaleCodes()->willReturn(['en_US', 'fr_FR']);

        $this->beConstructedWith($localeRepository, $entityWithFamilyVariantNormalizer);
    }

    function it_normalizes_a_root_product_model(
        $entityWithFamilyVariantNormalizer,
        FamilyVariantInterface $familyVariant,
        Collection $attributeSets,
        \ArrayIterator $attributeSetsIterator,
        VariantAttributeSetInterface $attributeSetOne,
        VariantAttributeSetInterface $attributeSetTwo,
        ProductModelInterface $rootProductModel
    ) {
        // Attribute sets of the family variant
        $attributeSets->getIterator()->willReturn($attributeSetsIterator);
        $attributeSetsIterator->rewind()->shouldBeCalled();
        $attributeSetsIterator->valid()->willReturn(true, true, false);
        $attributeSetsIterator->current()->willReturn($attributeSetOne, $attributeSetTwo);
        $attributeSetsIterator->next()->shouldBeCalled();

        $familyVariant->getVariantAttributeSets()->willReturn($attributeSets);

        $attributeSetOne->getLevel()->willReturn(1);
        $attributeSetOne->getAxesLabels('en_US')->willReturn(['Metric']);
        $attributeSetOne->getAxesLabels('fr_FR')->willReturn(['Metrique']);

        $attributeSetTwo->getLevel()->willReturn(2);
        $attributeSetTwo->getAxesLabels('en_US')->willReturn(['Size']);
        $attributeSetTwo->getAxesLabels('fr_FR')->willReturn(['Taille']);

        // Test start
        $rootProductModel->getFamilyVariant()->willReturn($familyVariant);
        $rootProductModel->getParent()->willReturn(null);

        $entityWithFamilyVariantNormalizer->normalize($rootProductModel, 'internal_api', [])
            ->willReturn(['ROOT PRODUCT MODEL NORMALIZED']);

        $this->normalize($rootProductModel, 'internal_api')->shouldReturn([
            0 => [
                'selected' => ['ROOT PRODUCT MODEL NORMALIZED']
            ],
            1 => [
                'axes'     => [
                    'en_US' => 'Metric',
                    'fr_FR' => 'Metrique',
                ]
            ],
            2 => [
                'axes'     => [
                    'en_US' => 'Size',
                    'fr_FR' => 'Taille',
                ]
            ]
        ]);
    }

    function it_normalizes_a_sub_product_model(
        $entityWithFamilyVariantNormalizer,
        FamilyVariantInterface $familyVariant,
        Collection $attributeSets,
        \ArrayIterator $attributeSetsIterator,
        VariantAttributeSetInterface $attributeSetOne,
        VariantAttributeSetInterface $attributeSetTwo,
        ProductModelInterface $rootProductModel,
        ProductModelInterface $productModel
    ) {
        // Attribute sets of the family variant
        $attributeSets->getIterator()->willReturn($attributeSetsIterator);
        $attributeSetsIterator->rewind()->shouldBeCalled();
        $attributeSetsIterator->valid()->willReturn(true, true, false);
        $attributeSetsIterator->current()->willReturn($attributeSetOne, $attributeSetTwo);
        $attributeSetsIterator->next()->shouldBeCalled();

        $familyVariant->getVariantAttributeSets()->willReturn($attributeSets);

        $attributeSetOne->getLevel()->willReturn(1);
        $attributeSetOne->getAxesLabels('en_US')->willReturn(['Metric']);
        $attributeSetOne->getAxesLabels('fr_FR')->willReturn(['Metrique']);

        $attributeSetTwo->getLevel()->willReturn(2);
        $attributeSetTwo->getAxesLabels('en_US')->willReturn(['Size']);
        $attributeSetTwo->getAxesLabels('fr_FR')->willReturn(['Taille']);

        // Test start
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $productModel->getParent()->willReturn($rootProductModel);

        $entityWithFamilyVariantNormalizer->normalize($rootProductModel, 'internal_api', [])
            ->willReturn(['ROOT PRODUCT MODEL NORMALIZED']);

        $entityWithFamilyVariantNormalizer->normalize($productModel, 'internal_api', [])
            ->willReturn(['PRODUCT MODEL NORMALIZED']);

        $this->normalize($productModel, 'internal_api')->shouldReturn([
            0 => [
                'selected' => ['ROOT PRODUCT MODEL NORMALIZED']
            ],
            1 => [
                'axes'     => [
                    'en_US' => 'Metric',
                    'fr_FR' => 'Metrique',
                ],
                'selected' => ['PRODUCT MODEL NORMALIZED'],
            ],
            2 => [
                'axes'     => [
                    'en_US' => 'Size',
                    'fr_FR' => 'Taille',
                ],
            ]
        ]);
    }

    function it_normalizes_a_variant_product(
        $entityWithFamilyVariantNormalizer,
        FamilyVariantInterface $familyVariant,
        Collection $attributeSets,
        \ArrayIterator $attributeSetsIterator,
        VariantAttributeSetInterface $attributeSetOne,
        VariantAttributeSetInterface $attributeSetTwo,
        ProductModelInterface $rootProductModel,
        ProductModelInterface $productModel,
        ProductInterface $variantProduct
    ) {
        // Attribute sets of the family variant
        $attributeSets->getIterator()->willReturn($attributeSetsIterator);
        $attributeSetsIterator->rewind()->shouldBeCalled();
        $attributeSetsIterator->valid()->willReturn(true, true, false);
        $attributeSetsIterator->current()->willReturn($attributeSetOne, $attributeSetTwo);
        $attributeSetsIterator->next()->shouldBeCalled();

        $familyVariant->getVariantAttributeSets()->willReturn($attributeSets);

        $attributeSetOne->getLevel()->willReturn(1);
        $attributeSetOne->getAxesLabels('en_US')->willReturn(['Metric']);
        $attributeSetOne->getAxesLabels('fr_FR')->willReturn(['Metrique']);

        $attributeSetTwo->getLevel()->willReturn(2);
        $attributeSetTwo->getAxesLabels('en_US')->willReturn(['Size']);
        $attributeSetTwo->getAxesLabels('fr_FR')->willReturn(['Taille']);

        // Test start
        $variantProduct->getFamilyVariant()->willReturn($familyVariant);
        $variantProduct->getParent()->willReturn($productModel);
        $productModel->getParent()->willReturn($rootProductModel);

        $entityWithFamilyVariantNormalizer->normalize($rootProductModel, 'internal_api', [])
            ->willReturn(['ROOT PRODUCT MODEL NORMALIZED']);

        $entityWithFamilyVariantNormalizer->normalize($productModel, 'internal_api', [])
            ->willReturn(['PRODUCT MODEL NORMALIZED']);

        $entityWithFamilyVariantNormalizer->normalize($variantProduct, 'internal_api', [])
            ->willReturn(['VARIANT PRODUCT NORMALIZED']);

        $this->normalize($variantProduct, 'internal_api')->shouldReturn([
            0 => [
                'selected' => ['ROOT PRODUCT MODEL NORMALIZED']
            ],
            1 => [
                'axes'     => [
                    'en_US' => 'Metric',
                    'fr_FR' => 'Metrique',
                ],
                'selected' => ['PRODUCT MODEL NORMALIZED'],
            ],
            2 => [
                'axes'     => [
                    'en_US' => 'Size',
                    'fr_FR' => 'Taille',
                ],
                'selected' => ['VARIANT PRODUCT NORMALIZED'],
            ]
        ]);
    }

    function it_throws_an_exception_if_it_is_not_a_variant_product_nor_a_product_model(
        \stdClass $entity
    ) {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'normalize', [$entity, 'internal_api']
        );
    }
}
