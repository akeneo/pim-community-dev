<?php

namespace Specification\Akeneo\Pim\Structure\Component\Normalizer\Standard;

use Akeneo\Pim\Structure\Component\Normalizer\Standard\FamilyNormalizer;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRequirementRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FamilyNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        CollectionFilterInterface $filter,
        AttributeRepositoryInterface $attributeRepository,
        AttributeRequirementRepositoryInterface $requirementsRepository
    ) {
        $this->beConstructedWith($normalizer, $filter, $attributeRepository, $requirementsRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_standard_normalization(FamilyInterface $family)
    {
        $this->supportsNormalization($family, 'standard')->shouldReturn(true);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
        $this->supportsNormalization($family, 'xml')->shouldReturn(false);
        $this->supportsNormalization($family, 'json')->shouldReturn(false);
    }

    function it_normalizes_family(
        $normalizer,
        $attributeRepository,
        $requirementsRepository,
        $filter,
        FamilyInterface $family,
        AttributeInterface $name,
        AttributeInterface $image,
        AttributeInterface $description,
        AttributeInterface $price
    ) {
        $attributeRepository->findAttributesByFamily($family)->willReturn([$name, $image, $description, $price]);
        $requirementsRepository->findRequiredAttributesCodesByFamily($family)->willReturn([
            ['attribute' => 'name', 'channel' => 'ecommerce'],
            ['attribute' => 'price', 'channel' => 'ecommerce'],
            ['attribute' => 'name', 'channel' => 'mobile'],
            ['attribute' => 'price', 'channel' => 'mobile']
        ]);

        $filter->filterCollection([$name, $image, $description, $price], 'pim.internal_api.attribute.view')
            ->willReturn([$price, $name, $image]);

        $normalizer->normalize(Argument::cetera())->willReturn([]);
        $family->getCode()->willReturn('mugs');
        $family->getAttributeAsLabel()->willReturn($name);
        $family->getAttributeAsImage()->willReturn($image);
        $name->getCode()->willReturn('name');
        $image->getCode()->willReturn('image');
        $price->getCode()->willReturn('price');

        $this->normalize($family)->shouldReturn(
            [
                'code'                   => 'mugs',
                'attributes'             => ['image', 'name', 'price'],
                'attribute_as_label'     => 'name',
                'attribute_as_image'     => 'image',
                'attribute_requirements' => ['ecommerce' => ['name', 'price'], 'mobile' => ['name', 'price']],
                'labels' => []
            ]
        );
    }
}
