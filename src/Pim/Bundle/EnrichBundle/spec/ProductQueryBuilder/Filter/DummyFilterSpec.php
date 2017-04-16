<?php

namespace spec\Pim\Bundle\EnrichBundle\ProductQueryBuilder\Filter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;

class DummyFilterSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            ['acme_attribute_type'],
            ['enabled', 'completeness'],
            ['ALL']
        );
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\FieldFilterInterface');
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->supportsOperator('ALL')->shouldReturn(true);
        $this->supportsOperator('IN')->shouldReturn(false);
    }

    function it_checks_if_field_is_supported()
    {
        $this->supportsField('categories')->shouldReturn(false);
        $this->supportsField('enabled')->shouldReturn(true);
        $this->supportsField('completeness')->shouldReturn(true);
        $this->supportsField('family')->shouldReturn(false);
    }

    function it_checks_if_attribute_is_supported(
        AttributeInterface $goodAttribute,
        AttributeInterface $badAttribute
    ) {
        $goodAttribute->getType()->willReturn('acme_attribute_type');
        $badAttribute->getType()->willReturn('acme_other_attribute_type');

        $this->supportsAttribute($goodAttribute)->shouldReturn(true);
        $this->supportsAttribute($badAttribute)->shouldReturn(false);
    }

    function it_returns_supported_fields()
    {
        $this->getFields()->shouldReturn(['enabled', 'completeness']);
    }

    function it_returns_supported_attribute_types()
    {
        $this->getAttributeTypes()->shouldReturn(['acme_attribute_type']);
    }
}
