<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsImageReference;
use PhpSpec\ObjectBehavior;

class AttributeAsImageReferenceSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromAttributeIdentifier', [AttributeIdentifier::fromString('image')]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeAsImageReference::class);
    }

    function it_can_be_constructed_with_no_attribute()
    {
        $this->beConstructedThrough('noReference', []);
    }

    function it_can_be_constructed_with_an_attribute_identifier()
    {
        $this->beConstructedThrough('createFromNormalized', ['picture']);
        $this->normalize()->shouldReturn('picture');
    }

    function it_can_be_constructed_with_no_attribute_identifier()
    {
        $this->beConstructedThrough('createFromNormalized', [null]);
        $this->normalize()->shouldReturn(null);
    }

    function it_normalizes_itself_when_instanciated_with_an_attribute_identifier()
    {
        $this->normalize()->shouldReturn('image');
    }

    function it_normalizes_itself_when_instanciated_with_no_attribute()
    {
        $this->beConstructedThrough('noReference', []);
        $this->normalize()->shouldReturn(null);
    }
}
