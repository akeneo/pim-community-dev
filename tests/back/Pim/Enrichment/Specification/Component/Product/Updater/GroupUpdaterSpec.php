<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupTranslation;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Structure\Component\Model\GroupTypeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\GroupTypeRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\GroupUpdater;

class GroupUpdaterSpec extends ObjectBehavior
{
    function let(
        GroupTypeRepositoryInterface $groupTypeRepository,
        AttributeRepositoryInterface $attributeRepository,
        ProductQueryBuilderFactoryInterface $pqbFactory
    ) {
        $this->beConstructedWith($groupTypeRepository, $attributeRepository, $pqbFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GroupUpdater::class);
    }

    function it_is_a_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_throws_an_exception_when_trying_to_update_anything_else_than_a_group()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                GroupInterface::class
            )
        )->during(
            'update',
            [new \stdClass(), []]
        );
    }

    function it_updates_a_group(
        $groupTypeRepository,
        $attributeRepository,
        $pqbFactory,
        GroupInterface $group,
        GroupTypeInterface $type,
        GroupTranslation $translatable,
        AttributeInterface $attributeColor,
        AttributeInterface $attributeSize,
        ProductInterface $removedProduct,
        ProductInterface $addedProduct,
        ProductQueryBuilderInterface $pqb
    ) {
        $groupTypeRepository->findOneByIdentifier('RELATED')->willReturn($type);
        $attributeRepository->findOneByIdentifier('color')->willReturn($attributeColor);
        $attributeRepository->findOneByIdentifier('size')->willReturn($attributeSize);

        $pqbFactory->create()->willReturn($pqb);
        $pqb->addFilter('identifier', 'IN', ['foo'])->shouldBeCalled();
        $pqb->execute()->willReturn([$addedProduct]);

        $group->getTranslation()->willReturn($translatable);
        $translatable->setLabel('T-shirt super beau')->shouldBeCalled();
        $group->setCode('mycode')->shouldBeCalled();
        $group->setLocale('fr_FR')->shouldBeCalled();
        $group->setType($type)->shouldBeCalled();
        $group->getId()->willReturn(null);

        $group->removeProduct($removedProduct)->shouldBeCalled();
        $group->addProduct($addedProduct)->shouldBeCalled();
        $group->getProducts()->willReturn([$removedProduct]);

        $values = [
            'code'     => 'mycode',
            'type'     => 'RELATED',
            'labels'   => [
                'fr_FR' => 'T-shirt super beau',
            ],
            'products' => ['foo']
        ];

        $this->update($group, $values, []);
    }

    function it_throws_an_error_if_type_is_unknown($groupTypeRepository, GroupInterface $group)
    {
        $group->setCode('mycode')->shouldBeCalled();
        $groupTypeRepository->findOneByIdentifier('UNKNOWN')->willReturn(null);

        $values = [
            'code' => 'mycode',
            'type' => 'UNKNOWN',
        ];

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'type',
                'group type',
                'The group type does not exist',
                GroupUpdater::class,
                'UNKNOWN'
            )
        )->during('update', [$group, $values, []]);
    }
}
