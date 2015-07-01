<?php

namespace spec\Pim\Component\Connector\ArrayConverter\Flat\Product;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Repository\GroupTypeRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\Flat\Product\AssociationColumnsResolver;
use Pim\Component\Connector\ArrayConverter\Flat\Product\FieldSplitter;

class FieldConverterSpec extends ObjectBehavior
{
    function let(
        FieldSplitter $fieldSplitter,
        AssociationColumnsResolver $assocFieldResolver,
        GroupTypeRepositoryInterface $groupTypeRepository
    ) {
        $this->beConstructedWith($fieldSplitter, $assocFieldResolver, $groupTypeRepository);
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

    function it_extracts_variant_group_from_column_group($assocFieldResolver, $fieldSplitter, $groupTypeRepository)
    {
        $assocFieldResolver->resolveAssociationColumns()->willReturn(['X_SELL-groups', 'associations']);
        $fieldSplitter->splitCollection('group1,variant_group1,group2')->willReturn([
            'group1',
            'variant_group1',
            'group2'
        ]);
        $groupTypeRepository->getTypeByGroup('variant_group1')->willReturn('1');
        $groupTypeRepository->getTypeByGroup('group1')->willReturn('0');
        $groupTypeRepository->getTypeByGroup('group2')->willReturn('0');

        $this->convert('groups', 'group1,variant_group1,group2')->shouldReturn([
            'groups'        => ['group1', 'group2'],
            'variant_group' => 'variant_group1'
        ]);
    }

    function it_throws_exception_when_many_variant_groups_are_passed($assocFieldResolver, $fieldSplitter, $groupTypeRepository)
    {
        $assocFieldResolver->resolveAssociationColumns()->willReturn(['X_SELL-groups', 'associations']);
        $fieldSplitter->splitCollection('group1,variant_group1,variant_group2')->willReturn([
            'group1',
            'variant_group1',
            'variant_group2'
        ]);
        $groupTypeRepository->getTypeByGroup('group1')->willReturn('0');
        $groupTypeRepository->getTypeByGroup('variant_group1')->willReturn('1');
        $groupTypeRepository->getTypeByGroup('variant_group2')->willReturn('1');

        $this->shouldThrow(
            new \InvalidArgumentException(
                'The product cannot belong to many variant groups: variant_group1, variant_group2'
            )
        )
        ->duringConvert('groups', 'group1,variant_group1,variant_group2');
    }
}
