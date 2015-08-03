<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Filter;

use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Component\Classification\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CategoryRightFilterSpec extends ObjectBehavior
{
    function let(
        TokenStorageInterface $tokenStorage,
        CategoryAccessRepository $categoryAccessRepo,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->beConstructedWith($tokenStorage, $categoryAccessRepo, $authorizationChecker);
    }

    function it_filters_a_category_collection_depending_on_user_s_permissions(
        $tokenStorage,
        $categoryAccessRepo,
        CategoryInterface $bootCategory,
        CategoryInterface $shirtCategory,
        TokenInterface $token,
        User $user
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $categoryAccessRepo->getGrantedCategoryIds($user, Attributes::VIEW_PRODUCTS)->willReturn([3,4]);

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
        $authorizationChecker->isGranted(Attributes::VIEW_PRODUCTS, $category)->willReturn(true);
        $this->filterObject($category, 'view')->shouldReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW_PRODUCTS, $category)->willReturn(false);
        $this->filterObject($category, 'view')->shouldReturn(true);
    }

    function it_throws_an_exception_if_filtered_object_is_not_a_category(ProductInterface $product)
    {
        $this->shouldThrow('\LogicException')->during('filterObject', [$product, 'view']);
    }

    function it_supports_categories(CategoryInterface $category, ProductInterface $product)
    {
        $this->supportsObject($category, 'view')->shouldReturn(true);
        $this->supportsObject($product, 'view')->shouldReturn(false);
    }
}
