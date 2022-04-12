<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueValuesSet;
use Ramsey\Uuid\Uuid;

class UniqueValuesSetSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UniqueValuesSet::class);
    }

    function it_could_add_two_times_the_same_value(
        ValueInterface $value,
        ProductInterface $product
    ) {
        $product->getUuid()->willReturn(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'));
        $product->getCreated()->willReturn(new \DateTime('2017-01-01T01:03:34+01:00'));
        $value->__toString()->willReturn('jean');

        $value->getAttributeCode()->willReturn('identifier');

        $this->addValue($value, $product)->shouldReturn(true);
        $this->addValue($value, $product)->shouldReturn(true);
    }

    function it_cannot_add_two_times_the_same_value_if_the_products_do_not_exist(
        ValueInterface $value1,
        ProductInterface $product1,
        ValueInterface $value2,
        ProductInterface $product2
    ) {
        $product1->getCreated()->willReturn(null);
        $product2->getCreated()->willReturn(null);
        $value1->__toString()->willReturn('jean');
        $value2->__toString()->willReturn('jean');
        $value1->getAttributeCode()->willReturn('identifier');
        $value2->getAttributeCode()->willReturn('identifier');

        $this->addValue($value1, $product1)->shouldReturn(true);
        $this->addValue($value2, $product2)->shouldReturn(false);
    }

    function it_cannot_add_two_times_similar_value_with_different_case_if_the_products_do_not_exist(
        ValueInterface $value1,
        ProductInterface $product1,
        ValueInterface $value2,
        ProductInterface $product2
    ) {
        $product1->getCreated()->willReturn(null);
        $product2->getCreated()->willReturn(null);
        $value1->__toString()->willReturn('Jean');
        $value2->__toString()->willReturn('jean');
        $value1->getAttributeCode()->willReturn('identifier');
        $value2->getAttributeCode()->willReturn('identifier');

        $this->addValue($value1, $product1)->shouldReturn(true);
        $this->addValue($value2, $product2)->shouldReturn(false);
    }

}
