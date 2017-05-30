<?php

namespace spec\Pim\Component\Catalog\Normalizer\Standard;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRequirementRepositoryInterface;
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
        $this->shouldHaveType('Pim\Component\Catalog\Normalizer\Standard\FamilyNormalizer');
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
        AttributeInterface $description,
        AttributeInterface $price
    ) {
        $attributeRepository->findAttributesByFamily($family)->willReturn([$name, $description, $price]);
        $requirementsRepository->findRequiredAttributesCodesByFamily($family)->willReturn([
            ['attribute' => 'name', 'channel' => 'ecommerce'],
            ['attribute' => 'price', 'channel' => 'ecommerce'],
            ['attribute' => 'name', 'channel' => 'mobile'],
            ['attribute' => 'price', 'channel' => 'mobile']
        ]);

        $filter->filterCollection([$name, $description, $price], 'pim.internal_api.attribute.view')
            ->willReturn([$price, $name]);

        $normalizer->normalize(Argument::cetera())->willReturn([]);
        $family->getCode()->willReturn('mugs');
        $family->getAttributeAsLabel()->willReturn($name);
        $name->getCode()->willReturn('name');
        $price->getCode()->willReturn('price');

        $this->normalize($family)->shouldReturn(
            [
                'code'                   => 'mugs',
                'attributes'             => ['name', 'price'],
                'attribute_as_label'     => 'name',
                'attribute_requirements' => ['ecommerce' => ['name', 'price'], 'mobile' => ['name', 'price']],
                'labels' => []
            ]
        );
    }
}
