<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FamilyNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $familyNormalizer,
        NormalizerInterface $translationNormalizer,
        CollectionFilterInterface $collectionFilter,
        AttributeRepositoryInterface $attributeRepository,
        VersionManager $versionManager,
        NormalizerInterface $versionNormalizer
    ) {
        $this->beConstructedWith(
            $familyNormalizer,
            $translationNormalizer,
            $collectionFilter,
            $attributeRepository,
            $versionManager,
            $versionNormalizer
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

    function it_normalizes_family(
        AttributeRepositoryInterface $attributeRepository,
        AttributeGroupInterface $marketingAttributeGroup,
        FamilyInterface $family,
        NormalizerInterface $translationNormalizer,
        NormalizerInterface $familyNormalizer,
        VersionManager $versionManager,
        AttributeInterface $name,
        AttributeInterface $price
    ) {
        $family->getId()->willReturn(1);

        $normalizedFamily = [
            'code'                   => 'tshirts',
            'attributes'             => [
                'name',
            ],
            'attribute_as_label'     => 'name',
            'attribute_requirements' => [
                'ecommerce' => [
                    'name',
                    'price',
                ],
                'mobile' => [
                    'name',
                    'price',
                ]
            ],
            'labels' => [],
            'meta' => [
                'id' => 1,
                'form' => 'pim-family-edit-form',
                'created' => null,
                'updated' => null,
            ]
        ];

        $familyNormalizer->normalize($family, 'standard', ['apply_filters' => false])->willReturn($normalizedFamily);

        $familyNormalizer->normalize($family, 'standard', ['apply_filters' => false])->shouldBeCalled();

        $attributeRepository->findAttributesByFamily($family)->willReturn([$name, $price]);
        $attributeRepository->findBy(['code' => ['name', 'price']])->willReturn([$name, $price]);

        $translationNormalizer->normalize(Argument::cetera())->willReturn([]);
        $family->getCode()->willReturn('tshirts');
        $family->getAttributeAsLabel()->willReturn($name);

        $marketingAttributeGroup->getCode()->willReturn('marketing');

        $name->getCode()->willReturn('name');
        $name->getType()->willReturn('pim_catalog_text');
        $name->getGroup()->willReturn($marketingAttributeGroup);
        $name->getSortOrder()->willReturn(1);

        $price->getCode()->willReturn('price');
        $price->getType()->willReturn('pim_catalog_price_collection');
        $price->getGroup()->willReturn($marketingAttributeGroup);
        $price->getSortOrder()->willReturn(3);

        $versionManager->getOldestLogEntry($family)->shouldBeCalled();
        $versionManager->getNewestLogEntry($family)->shouldBeCalled();

        $this->normalize($family, null, ['apply_filters' => false])->shouldReturn(
            [
                'code'                   => 'tshirts',
                'attributes'             => [
                    [
                        'code' => 'name',
                        'type' => 'pim_catalog_text',
                        'group_code' => 'marketing',
                        'labels' => [],
                        'sort_order' => 1,
                    ],
                    [
                        'code' => 'price',
                        'type' => 'pim_catalog_price_collection',
                        'group_code' => 'marketing',
                        'labels' => [],
                        'sort_order' => 3,
                    ]
                ],
                'attribute_as_label'     => 'name',
                'attribute_requirements' => [
                    'ecommerce' => [
                        'name',
                        'price',
                    ],
                    'mobile' => [
                        'name',
                        'price',
                    ]
                ],
                'labels' => [],
                'meta' => [
                    'id' => 1,
                    'form' => 'pim-family-edit-form',
                    'created' => null,
                    'updated' => null,
                ]
            ]
        );
    }
}
