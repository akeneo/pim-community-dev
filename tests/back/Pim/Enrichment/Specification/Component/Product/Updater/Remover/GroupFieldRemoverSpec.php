<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Remover;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\FieldRemoverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\GroupFieldRemover;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

class GroupFieldRemoverSpec extends ObjectBehavior
{
    function let(GroupRepositoryInterface $groupRepository)
    {
        $this->beConstructedWith(
            $groupRepository,
            ['groups']
        );
    }

    function it_is_a_remover()
    {
        $this->shouldImplement(FieldRemoverInterface::class);
    }

    function it_supports_groups_field()
    {
        $this->supportsField('groups')->shouldReturn(true);
        $this->supportsField('categories')->shouldReturn(false);
    }

    function it_removes_groups_field(
        GroupRepositoryInterface $groupRepository,
        ProductInterface $product,
        GroupInterface $packGroup,
        GroupInterface $crossGroup
    ) {
        $groupRepository->findOneByIdentifier('pack')->willReturn($packGroup);
        $groupRepository->findOneByIdentifier('cross')->willReturn($crossGroup);

        $product->removeGroup($packGroup->getWrappedObject())->shouldBeCalled();
        $product->removeGroup($crossGroup->getWrappedObject())->shouldBeCalled();

        $this->removeFieldData($product, 'groups', ['pack', 'cross']);
    }

    function it_fails_if_the_group_code_does_not_exist(
        GroupRepositoryInterface $groupRepository,
        ProductInterface $product,
        GroupInterface $pack
    ) {
        $groupRepository->findOneByIdentifier('pack')->willReturn($pack);
        $groupRepository->findOneByIdentifier('not valid code')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'groups',
                'group code',
                'The group does not exist',
                GroupFieldRemover::class,
                'not valid code'
            )
        )->during('removeFieldData', [$product, 'groups', ['pack', 'not valid code']]);
    }

    function it_checks_valid_data_format(ProductInterface $product)
    {
        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'groups',
                GroupFieldRemover::class,
                'not an array'
            )
        )->during('removeFieldData', [$product, 'groups', 'not an array']);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'groups',
                'one of the group codes is not a string, "array" given',
                GroupFieldRemover::class,
                [['array of array']]
            )
        )->during('removeFieldData', [$product, 'groups', [['array of array']]]);
    }

    function it_throws_an_exception_if_the_subject_is_not_a_product()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                ProductModel::class,
                ProductInterface::class
            )
        )->during('removeFieldData', [new ProductModel(), 'groups', ['tshirts']]);
    }
}
