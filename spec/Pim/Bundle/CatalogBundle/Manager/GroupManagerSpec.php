<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupTypeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class GroupManagerSpec extends ObjectBehavior
{
    const ATTRIBUTE_CLASS  = 'Pim\Bundle\CatalogBundle\Entity\Attribute';
    const GROUP_CLASS      = 'Pim\Bundle\CatalogBundle\Entity\Group';
    const GROUP_TYPE_CLASS = 'Pim\Bundle\CatalogBundle\Entity\GroupType';
    const PRODUCT_CLASS    = 'Pim\Bundle\CatalogBundle\Model\Product';

    function let(
        RegistryInterface $registry,
        ProductRepositoryInterface $productRepository
    ) {
        $this->beConstructedWith(
            $registry,
            $productRepository,
            self::GROUP_CLASS,
            self::GROUP_TYPE_CLASS,
            self::PRODUCT_CLASS,
            self::ATTRIBUTE_CLASS
        );
    }

    function it_provides_available_axis(
        $registry,
        AttributeRepositoryInterface $attRepository,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2
    ) {
        $registry->getRepository(self::ATTRIBUTE_CLASS)->willReturn($attRepository);
        $attRepository->findAllAxis()->willReturn([$attribute1, $attribute2]);

        $this->getAvailableAxis()->shouldReturn([$attribute1, $attribute2]);
    }

    function it_provides_available_axis_as_a_sorted_choice(
        $registry,
        AttributeRepositoryInterface $attRepository,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2
    ) {
        $attribute1->getId()->willReturn(1);
        $attribute1->getLabel()->willReturn('Foo');

        $attribute2->getId()->willReturn(2);
        $attribute2->getLabel()->willReturn('Bar');

        $registry->getRepository(self::ATTRIBUTE_CLASS)->willReturn($attRepository);
        $attRepository->findAllAxis()->willReturn([$attribute1, $attribute2]);

        $this->getAvailableAxisChoices()->shouldReturn([2 => 'Bar', 1 => 'Foo']);
    }

    function it_provides_the_group_repository($registry, GroupRepositoryInterface $groupRepository)
    {
        $registry->getRepository(self::GROUP_CLASS)->willReturn($groupRepository);

        $this->getRepository()->shouldReturn($groupRepository);
    }

    function it_provides_the_group_type_repository(
        $registry,
        GroupTypeRepositoryInterface $groupTypeRepository
    ) {
        $registry->getRepository(self::GROUP_TYPE_CLASS)->willReturn($groupTypeRepository);

        $this->getGroupTypeRepository()->shouldReturn($groupTypeRepository);
    }

    function it_returns_an_array_containing_a_limited_number_of_product_groups_and_total_number_of_products(
        $productRepository,
        GroupInterface $group,
        ProductInterface $product
    ) {
        $productRepository->getProductsByGroup($group, 5)->willReturn([$product]);
        $productRepository->getProductCountByGroup($group)->willReturn(20);

        $this->getProductList($group, 5);
    }
}
