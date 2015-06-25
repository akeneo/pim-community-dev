<?php

namespace spec\Pim\Component\Connector\ArrayConverter\Flat\Product\ValueConverter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\Flat\Product\AssociationColumnsResolver;
use Pim\Component\Connector\ArrayConverter\Flat\Product\FieldSplitter;

class FieldConverterSpec extends ObjectBehavior
{
    function let(FieldSplitter $fieldSplitter, AssociationColumnsResolver $assocFieldResolver)
    {
        $this->beConstructedWith($fieldSplitter, $assocFieldResolver);
    }

    function it_supports_converter_column($assocFieldResolver)
    {
        $assocFieldResolver->resolveAssociationColumns()->willReturn(['X_SELL-groups', 'associations']);

        $this->supportsColumn('associations')->shouldReturn(true);
        $this->supportsColumn('enabled')->shouldReturn(true);
        $this->supportsColumn('family')->shouldReturn(true);
        $this->supportsColumn('categories')->shouldReturn(true);
        $this->supportsColumn('groups')->shouldReturn(true);
        $this->supportsColumn('X_SELL-groups')->shouldReturn(true);

        $this->supportsColumn('other')->shouldReturn(false);
    }

    function it_converts($assocFieldResolver, $fieldSplitter)
    {
        $assocFieldResolver->resolveAssociationColumns()->willReturn(['X_SELL-groups', 'associations']);

        $this->convert('enabled', 'true')->shouldReturn(['enabled' => true]);
        $this->convert('enabled', true)->shouldReturn(['enabled' => true]);

        $fieldSplitter->splitCollection('dry,wet')->willReturn(['dry', 'wet']);
        $fieldSplitter->splitCollection('group1,group2')->willReturn(['group1', 'group2']);
        $fieldSplitter->splitCollection('value,test')->willReturn(['value', 'test']);
        $fieldSplitter->splitFieldName('X_SELL-groups')->willReturn(['X_SELL', 'groups']);

        $this->convert('family', 'family_name')->shouldReturn(['family' => 'family_name']);

        $this->convert('categories', 'dry,wet')->shouldReturn(['categories' => ['dry', 'wet']]);
        $this->convert('groups', 'group1,group2')->shouldReturn(['groups' => ['group1', 'group2']]);

        $this->convert('X_SELL-groups', 'value,test')->shouldReturn(['associations' => ['X_SELL' => ['groups' => ['value', 'test']]]]);
    }
}
