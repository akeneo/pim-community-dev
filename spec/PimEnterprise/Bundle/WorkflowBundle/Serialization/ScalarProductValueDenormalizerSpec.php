<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Serialization;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Symfony\Component\Serializer\SerializerInterface;

class ScalarProductValueDenormalizerSpec extends ObjectBehavior
{
    function it_is_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_denormalization_of_identifier_type()
    {
        $this->supportsDenormalization([], 'pim_catalog_identifier')->shouldBe(true);
    }

    function it_supports_denormalization_of_text_type()
    {
        $this->supportsDenormalization([], 'pim_catalog_text')->shouldBe(true);
    }

    function it_supports_denormalization_of_textarea_type()
    {
        $this->supportsDenormalization([], 'pim_catalog_textarea')->shouldBe(true);
    }

    function it_supports_denormalization_of_number_type()
    {
        $this->supportsDenormalization([], 'pim_catalog_number')->shouldBe(true);
    }

    function it_denormalizes_data_in_the_context_instance(AbstractProductValue $value)
    {
        $value->setData('foo')->shouldBeCalled();

        $this->denormalize('foo', null, null, ['instance' => $value]);
    }

    function it_throws_exception_if_the_context_instance_is_not_passed()
    {
        $e = new \InvalidArgumentException('A product value instance must be provided inside the context');

        $this->shouldThrow($e)->duringDenormalize('foo', null);
    }

    function it_throws_exception_if_the_context_instance_is_not_a_product_value()
    {
        $e = new \InvalidArgumentException('A product value instance must be provided inside the context');

        $this->shouldThrow($e)->duringDenormalize('foo', null, null, ['instance' => true]);
    }
}
