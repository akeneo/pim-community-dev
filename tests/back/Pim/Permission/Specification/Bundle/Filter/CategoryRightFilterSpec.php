<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Filter;

use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CategoryRightFilterSpec extends ObjectBehavior
{
    function let(
        TokenStorageInterface $tokenStorage,
        CategoryAccessRepository $categoryAccessRepo,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenInterface $token
    ) {
        $tokenStorage->getToken()->willReturn($token);

        $this->beConstructedWith($tokenStorage, $authorizationChecker, $categoryAccessRepo);
    }

    function it_filters_a_category_collection_depending_on_user_s_permissions(
        $tokenStorage,
        $categoryAccessRepo,
        CategoryInterface $bootCategory,
        CategoryInterface $shirtCategory,
        TokenInterface $token,
        UserInterface $user
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $categoryAccessRepo->getGrantedCategoryIds($user, Attributes::VIEW_ITEMS)->willReturn([3,4]);

        $bootCategory->getId()->willReturn(1);
        $shirtCategory->getId()->willReturn(2);

        $this->filterCollection([$bootCategory, $shirtCategory], 'view')->shouldReturn([]);

        $shirtCategory->getId()->willReturn(3);

        $this->filterCollection([$bootCategory, $shirtCategory], 'view')->shouldReturn([1 => $shirtCategory]);
    }

    function it_filters_a_category_depending_on_user_s_permissions(
        $authorizationChecker,
        CategoryInterface $category
    ) {
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $category)->willReturn(true);
        $this->filterObject($category, 'view')->shouldReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $category)->willReturn(false);
        $this->filterObject($category, 'view')->shouldReturn(true);
    }

    function it_throws_an_exception_if_filtered_object_is_not_a_category(ProductInterface $product)
    {
        $this->shouldThrow(\LogicException::class)->during('filterObject', [$product, 'view']);
    }

    function it_supports_categories(CategoryInterface $category, ProductInterface $product)
    {
        $this->supportsObject($category, 'view')->shouldReturn(true);
        $this->supportsObject($product, 'view')->shouldReturn(false);
    }
}
