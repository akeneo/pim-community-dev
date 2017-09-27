<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\GroupTranslation;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface;

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
        $this->shouldHaveType('Pim\Component\Catalog\Updater\GroupUpdater');
    }

    function it_is_a_updater()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_throws_an_exception_when_trying_to_update_anything_else_than_a_variant_group()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                'Pim\Component\Catalog\Model\GroupInterface'
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
                'Pim\Component\Catalog\Updater\GroupUpdater',
                'UNKNOWN'
            )
        )->during('update', [$group, $values, []]);
    }
}
