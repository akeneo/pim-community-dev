<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Component\Product\UseCase\DuplicateProduct;

use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Product\Component\Product\UseCase\DuplicateProduct\RemoveUniqueAttributeValues;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetUniqueAttributeCodes;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RemoveUniqueAttributeValuesSpec extends ObjectBehavior
{
    function let(
        GetUniqueAttributeCodes $getUniqueAttributeCodes,
        AttributeRepository $attributeRepository
    ) {
        $this->beConstructedWith($getUniqueAttributeCodes, $attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RemoveUniqueAttributeValues::class);
    }

    function it_removes_unique_values_from_the_collection_and_return_it(
        $getUniqueAttributeCodes,
        WriteValueCollection $valueCollection
    ) {
        $attributeCodes = ['unique_attribute_code', 'non_unique_attribute_code'];
        $valueCollection->getAttributeCodes()->willReturn($attributeCodes);
        $getUniqueAttributeCodes->fromAttributeCodes(Argument::type('array'))->willReturn(['unique_attribute_code']);
        $valueCollection->removeByAttributeCode(Argument::type('string'))->shouldBeCalledOnce();

        $this->fromCollection($valueCollection)->shouldContain('unique_attribute_code');
    }

    function it_removes_unique_values_from_the_collection_and_return_it_without_the_identifier(
        $getUniqueAttributeCodes,
        $attributeRepository,
        WriteValueCollection $valueCollection
    ) {
        $attributeCodes = ['identifier_code', 'unique_attribute_code', 'non_unique_attribute_code'];
        $valueCollection->getAttributeCodes()->willReturn($attributeCodes);
        $getUniqueAttributeCodes->fromAttributeCodes(Argument::type('array'))->willReturn(['identifier_code', 'unique_attribute_code']);
        $attributeRepository->getIdentifierCode()->willReturn('identifier_code');
        $valueCollection->removeByAttributeCode(Argument::type('string'))->shouldBeCalled(2);

        $this->fromCollection($valueCollection)->shouldContain('unique_attribute_code');
    }
}
