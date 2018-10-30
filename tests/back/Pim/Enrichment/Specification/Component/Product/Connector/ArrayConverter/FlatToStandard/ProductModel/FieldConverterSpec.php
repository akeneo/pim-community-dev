<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ConvertedField;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldSplitter;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ProductModel\FieldConverter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FieldConverterSpec extends ObjectBehavior
{
    function let(
        FieldSplitter $fieldSplitter,
        AssociationColumnsResolver $assocFieldResolver
    ) {
        $this->beConstructedWith($fieldSplitter, $assocFieldResolver);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FieldConverter::class);
    }

    function it_is_a_field_converter()
    {
        $this->shouldImplement(FieldConverterInterface::class);
    }

    function it_converts_an_association($assocFieldResolver, $fieldSplitter)
    {
        $assocFieldResolver->resolveAssociationColumns()->willReturn(['X_SELL-groups']);
        $fieldSplitter->splitFieldName('X_SELL-groups')->willReturn(['X_SELL', 'groups']);
        $fieldSplitter->splitCollection('group1,group2')->willReturn(['group1', 'group2']);

        $this->convert('X_SELL-groups', 'group1,group2')
            ->shouldBeLike(new ConvertedField('associations', ['X_SELL' => ['groups' => ['group1', 'group2']]]));
    }

    function it_converts_categories($assocFieldResolver, $fieldSplitter)
    {
        $assocFieldResolver->resolveAssociationColumns()->willReturn([]);
        $fieldSplitter->splitCollection('dry,wet')->willReturn(['dry', 'wet']);
        $this->convert('categories', 'dry,wet')->shouldBeLike(new ConvertedField('categories', ['dry', 'wet']));
    }

    function it_converts_other_field($assocFieldResolver)
    {
        $assocFieldResolver->resolveAssociationColumns()->willReturn([]);
        $this->convert('family_variant', 'family_variant')
            ->shouldBeLike(new ConvertedField('family_variant', 'family_variant'));
    }

    function it_converts_the_value_of_the_other_fields_to_a_string($assocFieldResolver, $fieldSplitter)
    {
        $assocFieldResolver->resolveAssociationColumns()->willReturn([]);
        $this->convert('family_variant', 123456)
            ->shouldBeLike(new ConvertedField('family_variant', '123456'));

        $this->convert('code', 123456)
            ->shouldBeLike(new ConvertedField('code', '123456'));

        $this->convert('parent', 123456)
            ->shouldBeLike(new ConvertedField('parent', '123456'));
    }

    function it_only_converts_a_specific_column($assocFieldResolver)
    {
        $assocFieldResolver->resolveAssociationColumns()->willReturn(['upsell']);

        $this->supportsColumn('parent')->shouldReturn(true);
        $this->supportsColumn('code')->shouldReturn(true);
        $this->supportsColumn('family_variant')->shouldReturn(true);
        $this->supportsColumn('upsell')->shouldReturn(true);
        $this->supportsColumn('other')->shouldReturn(false);
    }
}
