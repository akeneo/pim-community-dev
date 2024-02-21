<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\ProductQueryBuilder\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

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
        $this->shouldImplement(FieldFilterInterface::class);
        $this->shouldImplement(AttributeFilterInterface::class);
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
