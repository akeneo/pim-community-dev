<?php

namespace spec\PimEnterprise\Component\Catalog\Security\Updater\Setter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Updater\Setter\FieldSetterInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

class GrantedCategoryFieldSetterSpec extends ObjectBehavior
{
    function let(FieldSetterInterface $categoryFieldSetter, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->beConstructedWith($categoryFieldSetter, $authorizationChecker, ['categories']);
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
        ProductInterface $product,
        CategoryInterface $categoryA,
        CategoryInterface $categoryB
    ) {
        $data = ['categoryA', 'categoryB'];

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

    function it_throws_an_exception_if_a_category_is_not_granted(
        $authorizationChecker,
        ProductInterface $product,
        CategoryInterface $categoryA,
        CategoryInterface $categoryB
    ) {
        $data = ['categoryA', 'categoryB'];

        $categoryB->getCode()->willReturn('categoryB');

        $product->getCategories()->willReturn([$categoryA, $categoryB]);
        $authorizationChecker->isGranted([Attributes::VIEW_ITEMS], $categoryA)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::VIEW_ITEMS], $categoryB)->willReturn(false);
        $authorizationChecker->isGranted([Attributes::OWN], $product)->willReturn(true, true);

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
        ProductInterface $product,
        CategoryInterface $categoryA,
        CategoryInterface $categoryB
    ) {
        $data = ['categoryA', 'categoryB'];

        $categoryB->getCode()->willReturn('categoryB');

        $product->getId()->willReturn(1);
        $product->getCategories()->willReturn([$categoryA, $categoryB]);
        $authorizationChecker->isGranted([Attributes::VIEW_ITEMS], $categoryA)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::VIEW_ITEMS], $categoryB)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::OWN], $product)->willReturn(true, false);

        $this->shouldThrow(
            new InvalidArgumentException('You should at least keep your product in one category on which you have an own permission.')
        )->during('setFieldData', [$product, 'categories', $data, []]);
    }
}
