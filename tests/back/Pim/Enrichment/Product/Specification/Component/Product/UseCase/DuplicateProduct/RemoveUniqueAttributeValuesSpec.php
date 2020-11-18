<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Component\Product\UseCase\DuplicateProduct;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Product\Component\Product\UseCase\DuplicateProduct\RemoveUniqueAttributeValues;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetUniqueAttributeCodes;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RemoveUniqueAttributeValuesSpec extends ObjectBehavior
{
    function let(
        GetUniqueAttributeCodes $getUniqueAttributeCodes,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($getUniqueAttributeCodes, $attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RemoveUniqueAttributeValues::class);
    }

    function it_removes_unique_values_from_the_collection_and_return_it(
        $getUniqueAttributeCodes,
        ProductInterface $product,
        WriteValueCollection $valueCollection
    ) {
        $attributeCodes = ['unique_attribute_code', 'non_unique_attribute_code'];
        $product->getValues()->willReturn($valueCollection);
        $valueCollection->getAttributeCodes()->willReturn($attributeCodes);
        $getUniqueAttributeCodes->all()->willReturn(['unique_attribute_code']);
        $valueCollection->removeByAttributeCode(Argument::type('string'))->shouldBeCalledOnce();
        $product->setValues($valueCollection)->shouldBeCalled();

        $this->fromProduct($product)->shouldReturn($product);
    }
}
