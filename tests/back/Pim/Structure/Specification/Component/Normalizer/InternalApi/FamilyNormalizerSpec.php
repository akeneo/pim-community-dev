<?php

namespace Specification\Akeneo\Pim\Structure\Component\Normalizer\InternalApi;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FamilyNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $familyNormalizer,
        NormalizerInterface $attributeNormalizer,
        CollectionFilterInterface $collectionFilter,
        AttributeRepositoryInterface $attributeRepository,
        VersionManager $versionManager,
        NormalizerInterface $versionNormalizer,
        NormalizerInterface $translationNormalizer
    ) {
        $this->beConstructedWith(
            $familyNormalizer,
            $attributeNormalizer,
            $collectionFilter,
            $attributeRepository,
            $versionManager,
            $versionNormalizer,
            $translationNormalizer
        );
    }

    function it_is_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_internal_api_and_family(FamilyInterface $family)
    {
        $this->supportsNormalization($family, 'internal_api')
            ->shouldReturn(true);
        $this->supportsNormalization(new \StdClass(), 'internal_api')
            ->shouldReturn(false);
        $this->supportsNormalization([], 'internal_api')
            ->shouldReturn(false);
        $this->supportsNormalization($family, 'standard')
            ->shouldReturn(false);
    }

    function it_normalizes_an_unextended_family($translationNormalizer, FamilyInterface $family)
    {
        $family->getCode()->willReturn('camcorders');
        $translationNormalizer->normalize($family, Argument::cetera())->willReturn([
            'fr_FR' => 'Caméscopes'
        ]);
        $this->normalize($family, null, ['expanded' => false])->shouldReturn([
            'code' => 'camcorders',
            'labels' => ['fr_FR' => 'Caméscopes']
        ]);
    }

    function it_normalizes_family_without_attributes_used_as_axis(
        $familyNormalizer,
        $attributeNormalizer,
        $collectionFilter,
        AttributeRepositoryInterface $attributeRepository,
        FamilyInterface $family,
        Collection $emptyCollection,
        \ArrayIterator $emptyCollectionIterator,
        VersionManager $versionManager,
        AttributeInterface $name,
        AttributeInterface $description,
        AttributeInterface $price
    ) {
        $family->getId()->willReturn(1);
        $family->getFamilyVariants()->willReturn($emptyCollection);
        $emptyCollection->getIterator()->willReturn($emptyCollectionIterator);
        $emptyCollectionIterator->valid()->willReturn(false);
        $emptyCollectionIterator->rewind()->shouldBeCalled();

        $normalizedFamily = [
            'code'       => 'tshirts',
            'attributes' => [
                'name',
                'description',
                'price',
            ],
            'attribute_as_label'     => 'name',
            'attribute_requirements' => [
                'ecommerce' => ['name', 'price'],
                'mobile'    => ['name', 'price'],
            ],
            'labels' => [],
            'meta'   => [
                'id'      => 1,
                'form'    => 'pim-family-edit-form',
                'created' => null,
                'updated' => null,
            ]
        ];

        $familyNormalizer->normalize($family, 'standard', [])
            ->shouldBeCalled()
            ->willReturn($normalizedFamily);

        $attributeRepository->findAttributesByFamily($family)->willReturn([$name, $price]);
        $collectionFilter->filterCollection([$name, $description, $price], 'pim.internal_api.attribute.view')
            ->willReturn([$name, $price]);

        $attributeRepository->findBy(['code' => ['name', 'price']])
            ->willReturn([$name, $price]);
        $collectionFilter->filterCollection([$name, $price], 'pim.internal_api.attribute.view')
            ->willReturn([$name, $price]);

        $name->getCode()->willReturn('name');
        $price->getCode()->willReturn('price');

        $attributeNormalizer->normalize($name, 'internal_api', [])->willReturn(
            [
                'code'       => 'name',
                'type'       => 'pim_catalog_text',
                'group'      => 'marketing',
                'labels'     => [],
                'sort_order' => 1,
            ]
        );

        $attributeNormalizer->normalize($price, 'internal_api', [])->willReturn(
            [
                'code'       => 'price',
                'type'       => 'pim_catalog_price_collection',
                'group'      => 'marketing',
                'labels'     => [],
                'sort_order' => 3,
            ]
        );

        $family->getCode()->willReturn('tshirts');
        $family->getAttributeAsLabel()->willReturn($name);

        $versionManager->getOldestLogEntry($family)->shouldBeCalled();
        $versionManager->getNewestLogEntry($family)->shouldBeCalled();

        $this->normalize($family)->shouldReturn(
            [
                'code'       => 'tshirts',
                'attributes' => [
                    [
                        'code'       => 'name',
                        'type'       => 'pim_catalog_text',
                        'group'      => 'marketing',
                        'labels'     => [],
                        'sort_order' => 1,
                    ],
                    [
                        'code'       => 'price',
                        'type'       => 'pim_catalog_price_collection',
                        'group'      => 'marketing',
                        'labels'     => [],
                        'sort_order' => 3,
                    ]
                ],
                'attribute_as_label'     => 'name',
                'attribute_requirements' => [
                    'ecommerce' => ['name', 'price'],
                    'mobile'    => ['name', 'price'],
                ],
                'labels' => [],
                'meta' => [
                    'id'      => 1,
                    'form'    => 'pim-family-edit-form',
                    'created' => null,
                    'updated' => null,
                    'attributes_used_as_axis' => []
                ]
            ]
        );
    }

    function it_normalizes_family_with_attributes_used_as_axis_from_multiple_family_variants(
        $familyNormalizer,
        $attributeNormalizer,
        $collectionFilter,
        AttributeRepositoryInterface $attributeRepository,
        FamilyInterface $family,
        Collection $emptyCollection,
        \ArrayIterator $emptyCollectionIterator,
        FamilyVariantInterface $familyVariant1,
        Collection $familyVariantAxes1,
        FamilyVariantInterface $familyVariant2,
        Collection $familyVariantAxes2,
        VersionManager $versionManager,
        AttributeInterface $name,
        AttributeInterface $description,
        AttributeInterface $price,
        AttributeInterface $attributeUsedInTwoFamilyVariantsAsAxis,
        AttributeInterface $axis1,
        AttributeInterface $axis2,
        AttributeInterface $axis3,
        AttributeInterface $axis4
    ) {
        $family->getId()->willReturn(1);
        $family->getFamilyVariants()->willReturn($emptyCollection);
        $emptyCollection->getIterator()->willReturn($emptyCollectionIterator);
        $emptyCollectionIterator->current()->willReturn($familyVariant1, $familyVariant2);
        $emptyCollectionIterator->valid()->willReturn(true, true, false);
        $emptyCollectionIterator->next()->shouldBeCalled();
        $emptyCollectionIterator->rewind()->shouldBeCalled();

        $familyVariant1->getAxes()->willReturn($familyVariantAxes1);
        $familyVariantAxes1->toArray()->willReturn(
            [
                $attributeUsedInTwoFamilyVariantsAsAxis,
                $axis1,
                $axis3
            ]
        );

        $familyVariant2->getAxes()->willReturn($familyVariantAxes2);
        $familyVariantAxes2->toArray()->willReturn(
            [
                $attributeUsedInTwoFamilyVariantsAsAxis,
                $axis2,
                $axis4,
            ]
        );

        $attributeUsedInTwoFamilyVariantsAsAxis->getCode()->willReturn(
            'attribute_used_in_two_family_variants'
        );
        $axis1->getCode()->willReturn('axis1');
        $axis2->getCode()->willReturn('axis2');
        $axis3->getCode()->willReturn('axis3');
        $axis4->getCode()->willReturn('axis4');

        $normalizedFamily = [
            'code'       => 'tshirts',
            'attributes' => [
                'name',
                'description',
                'price',
            ],
            'attribute_as_label'     => 'name',
            'attribute_requirements' => [
                'ecommerce' => ['name', 'price'],
                'mobile'    => ['name', 'price'],
            ],
            'labels' => [],
            'meta'   => [
                'id'      => 1,
                'form'    => 'pim-family-edit-form',
                'created' => null,
                'updated' => null,
            ]
        ];

        $familyNormalizer->normalize($family, 'standard', [])
            ->shouldBeCalled()
            ->willReturn($normalizedFamily);

        $attributeRepository->findAttributesByFamily($family)->willReturn([$name, $price]);
        $collectionFilter->filterCollection([$name, $description, $price], 'pim.internal_api.attribute.view')
            ->willReturn([$name, $price]);

        $attributeRepository->findBy(['code' => ['name', 'price']])
            ->willReturn([$name, $price]);
        $collectionFilter->filterCollection([$name, $price], 'pim.internal_api.attribute.view')
            ->willReturn([$name, $price]);

        $name->getCode()->willReturn('name');
        $price->getCode()->willReturn('price');

        $attributeNormalizer->normalize($name, 'internal_api', [])->willReturn(
            [
                'code'       => 'name',
                'type'       => 'pim_catalog_text',
                'group'      => 'marketing',
                'labels'     => [],
                'sort_order' => 1,
            ]
        );

        $attributeNormalizer->normalize($price, 'internal_api', [])->willReturn(
            [
                'code'       => 'price',
                'type'       => 'pim_catalog_price_collection',
                'group'      => 'marketing',
                'labels'     => [],
                'sort_order' => 3,
            ]
        );

        $family->getCode()->willReturn('tshirts');
        $family->getAttributeAsLabel()->willReturn($name);

        $versionManager->getOldestLogEntry($family)->shouldBeCalled();
        $versionManager->getNewestLogEntry($family)->shouldBeCalled();

        $this->normalize($family)->shouldReturn(
            [
                'code'       => 'tshirts',
                'attributes' => [
                    [
                        'code'       => 'name',
                        'type'       => 'pim_catalog_text',
                        'group'      => 'marketing',
                        'labels'     => [],
                        'sort_order' => 1,
                    ],
                    [
                        'code'       => 'price',
                        'type'       => 'pim_catalog_price_collection',
                        'group'      => 'marketing',
                        'labels'     => [],
                        'sort_order' => 3,
                    ]
                ],
                'attribute_as_label'     => 'name',
                'attribute_requirements' => [
                    'ecommerce' => ['name', 'price'],
                    'mobile'    => ['name', 'price'],
                ],
                'labels' => [],
                'meta' => [
                    'id'      => 1,
                    'form'    => 'pim-family-edit-form',
                    'created' => null,
                    'updated' => null,
                    'attributes_used_as_axis' => [
                        'attribute_used_in_two_family_variants',
                        'axis1',
                        'axis3',
                        'axis2',
                        'axis4',
                    ]
                ]
            ]
        );
    }
}
