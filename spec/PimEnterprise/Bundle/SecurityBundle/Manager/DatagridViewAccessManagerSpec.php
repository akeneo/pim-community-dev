<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Manager;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use Symfony\Component\Security\Core\User\UserInterface;

class DatagridViewAccessManagerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\SecurityBundle\Manager\DatagridViewAccessManager');
    }

    function let(
        AttributeRepositoryInterface $attributeRepository,
        CategoryRepositoryInterface $categoryRepository,
        AttributeGroupAccessManager $attributeGroupAccessManager,
        CategoryAccessManager $categoryAccessManager,
        DatagridView $view,
        UserInterface $user
    ) {
        $this->beConstructedWith(
            $attributeRepository,
            $categoryRepository,
            $attributeGroupAccessManager,
            $categoryAccessManager
        );

        $view->getColumns()->willReturn(['col1']);
        $view->getFilters()->willReturn('f[filter1]=10,f[category][value][categoryId]=3');
    }

    function it_does_not_grant_access_if_user_has_not_access_to_an_column(
        $view,
        $user,
        $attributeGroupAccessManager,
        $attributeRepository,
        AttributeInterface $attribute,
        AttributeGroupInterface $group
    ) {
        $attribute->getGroup()->willReturn($group);
        $attributeRepository->findOneBy(['code' => 'col1'])->willReturn($attribute);
        $attributeGroupAccessManager->isUserGranted($user, $group, Attributes::VIEW_ATTRIBUTES)->willReturn(false);

        $this->isUserGranted($user, $view, Attributes::VIEW)->shouldReturn(false);
    }

    function it_does_not_grant_access_if_user_has_not_access_to_a_filter(
        $view,
        $user,
        $attributeGroupAccessManager,
        $attributeRepository,
        AttributeInterface $attribute,
        AttributeGroupInterface $group
    ) {
        $attribute->getGroup()->willReturn($group);
        $attributeRepository->findOneBy(['code' => 'col1'])->willReturn(null);
        $attributeRepository->findOneBy(['code' => 'filter1'])->willReturn($attribute);
        $attributeGroupAccessManager->isUserGranted($user, $group, Attributes::VIEW_ATTRIBUTES)->willReturn(false);

        $this->isUserGranted($user, $view, Attributes::VIEW)->shouldReturn(false);
    }

    function it_does_not_grant_access_if_user_has_not_access_to_a_category_filter(
        $view,
        $user,
        $categoryRepository,
        $categoryAccessManager,
        $attributeRepository,
        CategoryInterface $category
    ) {
        $attributeRepository->findOneBy(['code' => 'col1'])->willReturn(null);
        $attributeRepository->findOneBy(['code' => 'filter1'])->willReturn(null);
        $attributeRepository->findOneBy(['code' => 'category'])->willReturn(null);

        $categoryRepository->find('3')->willReturn($category);
        $categoryAccessManager->isUserGranted($user, $category, Attributes::VIEW_ITEMS)->willReturn(false);

        $this->isUserGranted($user, $view, Attributes::VIEW)->shouldReturn(false);
    }

    function it_grants_access(
        $view,
        $user,
        $attributeGroupAccessManager,
        $attributeRepository,
        $categoryRepository,
        $categoryAccessManager,
        AttributeInterface $attribute,
        AttributeGroupInterface $group,
        CategoryInterface $category
    ) {
        $attribute->getGroup()->willReturn($group);
        $attributeRepository->findOneBy(['code' => 'col1'])->willReturn($attribute);
        $attributeRepository->findOneBy(['code' => 'filter1'])->willReturn($attribute);
        $attributeRepository->findOneBy(['code' => 'category'])->willReturn(null);
        $attributeGroupAccessManager->isUserGranted($user, $group, Attributes::VIEW_ATTRIBUTES)->willReturn(true);

        $categoryRepository->find('3')->willReturn($category);
        $categoryAccessManager->isUserGranted($user, $category, Attributes::VIEW_ITEMS)->willReturn(true);

        $this->isUserGranted($user, $view, Attributes::VIEW)->shouldReturn(true);
    }
}
