<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Repository\GroupTypeRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ConvertedField;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldSplitter;

class FieldConverterSpec extends ObjectBehavior
{
    function let(
        FieldSplitter $fieldSplitter,
        AssociationColumnsResolver $assocFieldResolver,
        GroupTypeRepositoryInterface $groupTypeRepository
    ) {
        $this->beConstructedWith($fieldSplitter, $assocFieldResolver, $groupTypeRepository);
    }

    function it_is_a_field_converter()
    {
        $this->shouldImplement(FieldConverterInterface::class);
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

        $this->convert('enabled', 'true')->shouldBeLike(new ConvertedField('enabled', true));
        $this->convert('enabled', true)->shouldBeLike(new ConvertedField('enabled', true));

        $fieldSplitter->splitCollection('dry,wet')->willReturn(['dry', 'wet']);
        $fieldSplitter->splitCollection('group1,group2')->willReturn(['group1', 'group2']);
        $fieldSplitter->splitCollection('value,test')->willReturn(['value', 'test']);
        $fieldSplitter->splitFieldName('X_SELL-groups')->willReturn(['X_SELL', 'groups']);

        $this->convert('family', 'family_name')->shouldBeLike(new ConvertedField('family', 'family_name'));

        $this->convert('categories', 'dry,wet')->shouldBeLike(new ConvertedField('categories', ['dry', 'wet']));
        $this->convert('groups', 'group1,group2')->shouldBeLike(new ConvertedField('groups', ['group1', 'group2']));

        $this->convert('X_SELL-groups', 'value,test')->shouldBeLike(
            new ConvertedField('associations', ['X_SELL' => ['groups' => ['value', 'test']]])
        );
    }

    function it_extracts_group_from_column_group($assocFieldResolver, $fieldSplitter, $groupTypeRepository)
    {
        $assocFieldResolver->resolveAssociationColumns()->willReturn(['X_SELL-groups', 'associations']);
        $fieldSplitter->splitCollection('group1,group2')->willReturn([
            'group1',
            'group2'
        ]);

        $this->convert('groups', 'group1,group2')->shouldBeLike(
            new ConvertedField('groups', ['group1', 'group2'])
        );
    }
}
