<?php

namespace spec\PimEnterprise\Component\Catalog\Security\Updater\Setter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Updater\Setter\FieldSetterInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

class GrantedCategoryFieldSetterSpec extends ObjectBehavior
{
    function let(
        FieldSetterInterface $categoryFieldSetter,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        CategoryAccessRepository $categoryAccessRepository
    ) {
        $this->beConstructedWith(
            $categoryFieldSetter,
            $authorizationChecker,
            $categoryAccessRepository,
            $tokenStorage,
            ['categories']
        );
    }

    function it_implements_a_filter_interface()
    {
        $this->shouldImplement(FieldSetterInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\Catalog\Security\Updater\Setter\GrantedCategoryFieldSetter');
    }

    function it_sets_categories(
        $authorizationChecker,
        $tokenStorage,
        $categoryAccessRepository,
        TokenInterface $token,
        UserInterface $user,
        ProductInterface $product,
        CategoryInterface $categoryA,
        CategoryInterface $categoryB
    ) {
        $data = ['categoryA', 'categoryB'];
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $product->getCategoryCodes()->willReturn([]);
        $categoryAccessRepository->areAllCategoryCodesGranted($user, Attributes::VIEW_ITEMS, [])->willReturn(true);

        $product->getCategories()->willReturn([$categoryA, $categoryB]);
        $authorizationChecker->isGranted([Attributes::VIEW_ITEMS], $categoryA)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::VIEW_ITEMS], $categoryB)->willReturn(true);

        $this->shouldNotThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'categories',
                'category code',
                'The category does not exist',
                'PimEnterprise\Component\Catalog\Security\Updater\Setter\GrantedCategoryFieldSetter',
                'categoryB'
            )
        )->during('setFieldData', [$product, 'categories', $data, []]);

        $this->shouldNotThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'categories',
                'category code',
                'The category does not exist',
                'PimEnterprise\Component\Catalog\Security\Updater\Setter\GrantedCategoryFieldSetter',
                'categoryA'
            )
        )->during('setFieldData', [$product, 'categories', $data, []]);
    }

    function it_throws_an_exception_if_user_lose_ownership_because_there_is_still_a_category_the_user_can_not_view(
        $authorizationChecker,
        $tokenStorage,
        $categoryAccessRepository,
        TokenInterface $token,
        UserInterface $user,
        ProductInterface $product
    ) {
        $data = ['categoryA', 'categoryB'];
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $product->getCategoryCodes()->willReturn(['categoryA', 'categoryB', 'categoryC']);
        $categoryAccessRepository
            ->areAllCategoryCodesGranted($user, Attributes::VIEW_ITEMS, ['categoryA', 'categoryB', 'categoryC'])
            ->willReturn(false);

        $product->getId()->willReturn(1);
        $product->getCategories()->willReturn([]);
        $authorizationChecker->isGranted([Attributes::OWN], $product)->willReturn(true);

        $this->shouldThrow(
            new InvalidArgumentException('You should at least keep your product in one category on which you have an own permission.')
        )->during('setFieldData', [$product, 'categories', $data, []]);
    }

    function it_throws_an_exception_if_a_category_is_not_granted(
        $authorizationChecker,
        $tokenStorage,
        $categoryAccessRepository,
        TokenInterface $token,
        UserInterface $user,
        ProductInterface $product,
        CategoryInterface $categoryA,
        CategoryInterface $categoryB
    ) {
        $data = ['categoryA', 'categoryB'];
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $product->getCategoryCodes()->willReturn([]);
        $categoryAccessRepository->areAllCategoryCodesGranted($user, Attributes::VIEW_ITEMS, [])->willReturn(true);

        $categoryB->getCode()->willReturn('categoryB');

        $product->getCategories()->willReturn([$categoryA, $categoryB]);
        $authorizationChecker->isGranted([Attributes::OWN], $product)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::VIEW_ITEMS], $categoryA)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::VIEW_ITEMS], $categoryB)->willReturn(false);
        $authorizationChecker->isGranted([Attributes::OWN_PRODUCTS], $categoryA)->willReturn(true);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'categories',
                'category code',
                'The category does not exist',
                'PimEnterprise\Component\Catalog\Security\Updater\Setter\GrantedCategoryFieldSetter',
                'categoryB'
            )
        )->during('setFieldData', [$product, 'categories', $data, []]);
    }

    function it_throws_an_exception_if_user_loses_the_ownership_on_a_product(
        $authorizationChecker,
        $tokenStorage,
        $categoryAccessRepository,
        TokenInterface $token,
        UserInterface $user,
        ProductInterface $product,
        CategoryInterface $categoryA,
        CategoryInterface $categoryB
    ) {
        $data = ['categoryA', 'categoryB'];
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $product->getCategoryCodes()->willReturn([]);
        $categoryAccessRepository
            ->areAllCategoryCodesGranted($user, Attributes::VIEW_ITEMS, [])
            ->willReturn(true);

        $categoryB->getCode()->willReturn('categoryB');

        $product->getId()->willReturn(1);
        $product->getCategories()->willReturn([$categoryA, $categoryB]);
        $authorizationChecker->isGranted([Attributes::OWN], $product)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::VIEW_ITEMS], $categoryA)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::VIEW_ITEMS], $categoryB)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::OWN_PRODUCTS], $categoryA)->willReturn(false);
        $authorizationChecker->isGranted([Attributes::OWN_PRODUCTS], $categoryB)->willReturn(false);

        $this->shouldThrow(
            new InvalidArgumentException('You should at least keep your product in one category on which you have an own permission.')
        )->during('setFieldData', [$product, 'categories', $data, []]);
    }
}
