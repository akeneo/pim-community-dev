<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Component\Product\UseCase\DuplicateProduct;

use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Product\Component\Product\UseCase\DuplicateProduct\RemoveUniqueAttributeValues;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RemoveUniqueAttributeValuesSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RemoveUniqueAttributeValues::class);
    }

    function it_removes_unique_values_from_the_collection(WriteValueCollection $valueCollection)
    {
        $valueCollection->getAttributeCodes()->willReturn(['sku', 'a_text']);
        $valueCollection->removeByAttributeCode(Argument::type('string'))->shouldBeCalled();

        $this->fromCollection($valueCollection, ['sku']);
    }
}
