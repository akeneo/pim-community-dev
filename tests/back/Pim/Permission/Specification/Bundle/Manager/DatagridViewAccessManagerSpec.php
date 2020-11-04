<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Manager;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Permission\Bundle\Manager\AttributeGroupAccessManager;
use Akeneo\Pim\Permission\Bundle\Manager\CategoryAccessManager;
use Akeneo\Pim\Permission\Bundle\Manager\DatagridViewAccessManager;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\UserManagement\Component\Model\User;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use PhpSpec\ObjectBehavior;

class DatagridViewAccessManagerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(DatagridViewAccessManager::class);
    }

    function let(
        AttributeRepositoryInterface $attributeRepository,
        CategoryRepositoryInterface $categoryRepository,
        AttributeGroupAccessManager $attributeGroupAccessManager,
        CategoryAccessManager $categoryAccessManager,
        DatagridView $view
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
        $attributeGroupAccessManager,
        $attributeRepository,
        AttributeInterface $attribute,
        AttributeGroupInterface $group
    ) {
        $user = new User();
        $attribute->getGroup()->willReturn($group);
        $attributeRepository->findOneBy(['code' => 'col1'])->willReturn($attribute);
        $attributeGroupAccessManager->isUserGranted($user, $group, Attributes::VIEW_ATTRIBUTES)->willReturn(false);

        $this->isUserGranted($user, $view, Attributes::VIEW)->shouldReturn(false);
    }

    function it_does_not_grant_access_if_user_has_not_access_to_a_filter(
        $view,
        $attributeGroupAccessManager,
        $attributeRepository,
        AttributeInterface $attribute,
        AttributeGroupInterface $group
    ) {
        $user = new User();
        $attribute->getGroup()->willReturn($group);
        $attributeRepository->findOneBy(['code' => 'col1'])->willReturn(null);
        $attributeRepository->findOneBy(['code' => 'filter1'])->willReturn($attribute);
        $attributeGroupAccessManager->isUserGranted($user, $group, Attributes::VIEW_ATTRIBUTES)->willReturn(false);

        $this->isUserGranted($user, $view, Attributes::VIEW)->shouldReturn(false);
    }

    function it_does_not_grant_access_if_user_has_not_access_to_a_category_filter(
        $view,
        $categoryRepository,
        $categoryAccessManager,
        $attributeRepository,
        CategoryInterface $category
    ) {
        $user = new User();
        $attributeRepository->findOneBy(['code' => 'col1'])->willReturn(null);
        $attributeRepository->findOneBy(['code' => 'filter1'])->willReturn(null);
        $attributeRepository->findOneBy(['code' => 'category'])->willReturn(null);

        $categoryRepository->find('3')->willReturn($category);
        $categoryAccessManager->isUserGranted($user, $category, Attributes::VIEW_ITEMS)->willReturn(false);

        $this->isUserGranted($user, $view, Attributes::VIEW)->shouldReturn(false);
    }

    function it_grants_access(
        $view,
        $attributeGroupAccessManager,
        $attributeRepository,
        $categoryRepository,
        $categoryAccessManager,
        AttributeInterface $attribute,
        AttributeGroupInterface $group,
        CategoryInterface $category
    ) {
        $user = new User();
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
