<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Normalizer\EntityWithFamilyVariantNormalizer;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

class VariantNavigationNormalizerSpec extends ObjectBehavior
{
    function let(
        LocaleRepositoryInterface $localeRepository,
        EntityWithFamilyVariantNormalizer $entityWithFamilyVariantNormalizer
    ) {
        $localeRepository->getActivatedLocaleCodes()->willReturn(['en_US', 'fr_FR']);

        $this->beConstructedWith($localeRepository, $entityWithFamilyVariantNormalizer);
    }

    function it_normalizes_a_root_product_model(
        $entityWithFamilyVariantNormalizer,
        FamilyVariantInterface $familyVariant,
        Collection $attributeSets,
        \ArrayIterator $attributeSetsIterator,
        Collection $axesSetsOne,
        \ArrayIterator $axesSetsOneIterator,
        Collection $axesSetsTwo,
        \ArrayIterator $axesSetsTwoIterator,
        VariantAttributeSetInterface $attributeSetOne,
        VariantAttributeSetInterface $attributeSetTwo,
        AttributeInterface $metricAttribute,
        AttributeInterface $sizeAttribute,
        ProductModelInterface $rootProductModel
    ) {
        // Attribute sets of the family variant
        $attributeSets->getIterator()->willReturn($attributeSetsIterator);
        $attributeSetsIterator->rewind()->shouldBeCalled();
        $attributeSetsIterator->valid()->willReturn(true, true, false);
        $attributeSetsIterator->current()->willReturn($attributeSetOne, $attributeSetTwo);
        $attributeSetsIterator->next()->shouldBeCalled();

        // First axes set, with metric as axis
        $axesSetsOne->getIterator()->willReturn($axesSetsOneIterator);
        $axesSetsOneIterator->rewind()->shouldBeCalled();
        $axesSetsOneIterator->valid()->willReturn(true, true, false);
        $axesSetsOneIterator->current()->willReturn($metricAttribute);
        $axesSetsOneIterator->next()->shouldBeCalled();

        // Second axes set, with size as axis
        $axesSetsTwo->getIterator()->willReturn($axesSetsTwoIterator);
        $axesSetsTwoIterator->rewind()->shouldBeCalled();
        $axesSetsTwoIterator->valid()->willReturn(true, true, false);
        $axesSetsTwoIterator->current()->willReturn($sizeAttribute);
        $axesSetsTwoIterator->next()->shouldBeCalled();

        $familyVariant->getVariantAttributeSets()->willReturn($attributeSets);

        $attributeSetOne->getLevel()->willReturn(1);
        $attributeSetOne->getAxes()->willReturn($axesSetsOne);

        $attributeSetTwo->getLevel()->willReturn(2);
        $attributeSetTwo->getAxes()->willReturn($axesSetsTwo);

        $sizeAttribute->setLocale('en_US')->shouldBeCalled();
        $sizeAttribute->setLocale('fr_FR')->shouldBeCalled();
        $metricAttribute->setLocale('en_US')->shouldBeCalled();
        $metricAttribute->setLocale('fr_FR')->shouldBeCalled();

        $sizeAttribute->getLabel()->willReturn('Size', 'Taille', 'Size', 'Taille');
        $metricAttribute->getLabel()->willReturn('Metric', 'Metrique', 'Metric', 'Metrique');

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
        Collection $axesSetsOne,
        \ArrayIterator $axesSetsOneIterator,
        Collection $axesSetsTwo,
        \ArrayIterator $axesSetsTwoIterator,
        VariantAttributeSetInterface $attributeSetOne,
        VariantAttributeSetInterface $attributeSetTwo,
        AttributeInterface $metricAttribute,
        AttributeInterface $sizeAttribute,
        ProductModelInterface $rootProductModel,
        ProductModelInterface $productModel
    ) {
        // Attribute sets of the family variant
        $attributeSets->getIterator()->willReturn($attributeSetsIterator);
        $attributeSetsIterator->rewind()->shouldBeCalled();
        $attributeSetsIterator->valid()->willReturn(true, true, false);
        $attributeSetsIterator->current()->willReturn($attributeSetOne, $attributeSetTwo);
        $attributeSetsIterator->next()->shouldBeCalled();

        // First axes set, with metric as axis
        $axesSetsOne->getIterator()->willReturn($axesSetsOneIterator);
        $axesSetsOneIterator->rewind()->shouldBeCalled();
        $axesSetsOneIterator->valid()->willReturn(true, true, false);
        $axesSetsOneIterator->current()->willReturn($metricAttribute);
        $axesSetsOneIterator->next()->shouldBeCalled();

        // Second axes set, with size as axis
        $axesSetsTwo->getIterator()->willReturn($axesSetsTwoIterator);
        $axesSetsTwoIterator->rewind()->shouldBeCalled();
        $axesSetsTwoIterator->valid()->willReturn(true, true, false);
        $axesSetsTwoIterator->current()->willReturn($sizeAttribute);
        $axesSetsTwoIterator->next()->shouldBeCalled();

        $familyVariant->getVariantAttributeSets()->willReturn($attributeSets);

        $attributeSetOne->getLevel()->willReturn(1);
        $attributeSetOne->getAxes()->willReturn($axesSetsOne);

        $attributeSetTwo->getLevel()->willReturn(2);
        $attributeSetTwo->getAxes()->willReturn($axesSetsTwo);

        $sizeAttribute->setLocale('en_US')->shouldBeCalled();
        $sizeAttribute->setLocale('fr_FR')->shouldBeCalled();
        $metricAttribute->setLocale('en_US')->shouldBeCalled();
        $metricAttribute->setLocale('fr_FR')->shouldBeCalled();

        $sizeAttribute->getLabel()->willReturn('Size', 'Taille', 'Size', 'Taille');
        $metricAttribute->getLabel()->willReturn('Metric', 'Metrique', 'Metric', 'Metrique');

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
        Collection $axesSetsOne,
        \ArrayIterator $axesSetsOneIterator,
        Collection $axesSetsTwo,
        \ArrayIterator $axesSetsTwoIterator,
        VariantAttributeSetInterface $attributeSetOne,
        VariantAttributeSetInterface $attributeSetTwo,
        AttributeInterface $metricAttribute,
        AttributeInterface $sizeAttribute,
        ProductModelInterface $rootProductModel,
        ProductModelInterface $productModel,
        VariantProductInterface $variantProduct
    ) {
        // Attribute sets of the family variant
        $attributeSets->getIterator()->willReturn($attributeSetsIterator);
        $attributeSetsIterator->rewind()->shouldBeCalled();
        $attributeSetsIterator->valid()->willReturn(true, true, false);
        $attributeSetsIterator->current()->willReturn($attributeSetOne, $attributeSetTwo);
        $attributeSetsIterator->next()->shouldBeCalled();

        // First axes set, with metric as axis
        $axesSetsOne->getIterator()->willReturn($axesSetsOneIterator);
        $axesSetsOneIterator->rewind()->shouldBeCalled();
        $axesSetsOneIterator->valid()->willReturn(true, true, false);
        $axesSetsOneIterator->current()->willReturn($metricAttribute);
        $axesSetsOneIterator->next()->shouldBeCalled();

        // Second axes set, with size as axis
        $axesSetsTwo->getIterator()->willReturn($axesSetsTwoIterator);
        $axesSetsTwoIterator->rewind()->shouldBeCalled();
        $axesSetsTwoIterator->valid()->willReturn(true, true, false);
        $axesSetsTwoIterator->current()->willReturn($sizeAttribute);
        $axesSetsTwoIterator->next()->shouldBeCalled();

        $familyVariant->getVariantAttributeSets()->willReturn($attributeSets);

        $attributeSetOne->getLevel()->willReturn(1);
        $attributeSetOne->getAxes()->willReturn($axesSetsOne);

        $attributeSetTwo->getLevel()->willReturn(2);
        $attributeSetTwo->getAxes()->willReturn($axesSetsTwo);

        $sizeAttribute->setLocale('en_US')->shouldBeCalled();
        $sizeAttribute->setLocale('fr_FR')->shouldBeCalled();
        $metricAttribute->setLocale('en_US')->shouldBeCalled();
        $metricAttribute->setLocale('fr_FR')->shouldBeCalled();

        $sizeAttribute->getLabel()->willReturn('Size', 'Taille', 'Size', 'Taille');
        $metricAttribute->getLabel()->willReturn('Metric', 'Metrique', 'Metric', 'Metrique');

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
