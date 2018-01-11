<?php

declare(strict_types=1);

namespace spec\PimEnterprise\Bundle\EnrichBundle\Doctrine\Counter;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class GrantedCategoryProductsCounterSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        CategoryRepositoryInterface $categoryRepository,
        CategoryAccessRepository $categoryAccessRepo,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->beConstructedWith(
            $pqbFactory,
            $categoryRepository,
            $categoryAccessRepo,
            $authorizationChecker,
            $tokenStorage
        );
    }

    function it_returns_zero_if_user_doesnt_have_access_to_category(
        $authorizationChecker,
        CategoryInterface $category
    ) {
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $category)->willReturn(false);

        $this->getItemsCountInCategory($category, false, true);
    }

    function it_gets_items_count_in_granted_category_without_children(
        $pqbFactory,
        $categoryAccessRepo,
        $authorizationChecker,
        $tokenStorage,
        CategoryInterface $category,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor,
        TokenInterface $token,
        UserInterface $user
    ) {
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $category)->willReturn(true);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $category->getCode()->willReturn('short');
        $categoryAccessRepo->getGrantedChildrenCodes($category, $user, Attributes::VIEW_ITEMS)->shouldNotBeCalled();

        $pqbFactory->create([
            'filters' => [
                [
                    'field' => 'categories',
                    'operator' => Operators::IN_LIST,
                    'value' => ['short']
                ]
            ]
        ])->willReturn($pqb);
        $pqb->execute()->willReturn($cursor);
        $cursor->count()->willReturn(114);

        $this->getItemsCountInCategory($category, false, true)->shouldReturn(114);
    }

    function it_gets_items_count_in_granted_category_with_children(
        $pqbFactory,
        $categoryAccessRepo,
        $authorizationChecker,
        $tokenStorage,
        CategoryInterface $category,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor,
        TokenInterface $token,
        UserInterface $user
    ) {
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $category)->willReturn(true);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $category->getCode()->willReturn('short');
        $categoryAccessRepo->getGrantedChildrenCodes($category, $user, Attributes::VIEW_ITEMS)->willReturn([
            'short', 'short_children', 'short_adults'
        ]);

        $pqbFactory->create([
            'filters' => [
                [
                    'field' => 'categories',
                    'operator' => Operators::IN_LIST,
                    'value' => ['short', 'short_children', 'short_adults']
                ]
            ]
        ])->willReturn($pqb);
        $pqb->execute()->willReturn($cursor);
        $cursor->count()->willReturn(1220);

        $this->getItemsCountInCategory($category, true, true)->shouldReturn(1220);
    }
}
