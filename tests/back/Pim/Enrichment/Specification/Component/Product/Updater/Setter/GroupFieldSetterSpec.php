<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FieldSetterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\GroupFieldSetter;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\SetterInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GroupFieldSetterSpec extends ObjectBehavior
{
    function let(
        GroupRepositoryInterface $groupRepository
    ) {
        $this->beConstructedWith(
            $groupRepository,
            ['groups']
        );
    }

    function it_is_a_setter()
    {
        $this->shouldImplement(SetterInterface::class);
        $this->shouldImplement(FieldSetterInterface::class);
    }

    function it_supports_groups_field()
    {
        $this->supportsField('groups')->shouldReturn(true);
        $this->supportsField('categories')->shouldReturn(false);
    }

    function it_checks_valid_data_format(ProductInterface $product)
    {
        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'groups',
                GroupFieldSetter::class,
                'not an array'
            )
        )->during('setFieldData', [$product, 'groups', 'not an array']);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'groups',
                'one of the group codes is not a string, "array" given',
                GroupFieldSetter::class,
                [['array of array']]
            )
        )->during('setFieldData', [$product, 'groups', [['array of array']]]);
    }

    function it_sets_groups_field(
        GroupRepositoryInterface $groupRepository,
        ProductInterface $product
    ) {
        $pack = (new Group())->setCode('pack');
        $groupRepository->findOneByIdentifier('pack')->willReturn($pack);
        $cross = (new Group())->setCode('cross');
        $groupRepository->findOneByIdentifier('cross')->willReturn($cross);

        $product->setGroups(
            Argument::that(
                function (Collection $groups) use ($pack, $cross) {
                    return $groups->getValues() === [$pack, $cross];
                }
            )
        )->shouldBeCalled();

        $this->setFieldData($product, 'groups', ['pack', 'cross']);
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
                GroupFieldSetter::class,
                'not valid code'
            )
        )->during('setFieldData', [$product, 'groups', ['pack', 'not valid code']]);
    }

    function it_throws_an_exception_if_the_subject_is_not_a_product()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                ProductModel::class,
                ProductInterface::class
            )
        )->during('setFieldData', [new ProductModel(), 'groups', ['tshirts']]);
    }
}
