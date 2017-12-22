<?php

namespace spec\PimEnterprise\Component\Catalog\Security\Merger;

use Akeneo\Component\Classification\CategoryAwareInterface;
use Akeneo\Component\Classification\Repository\ItemCategoryRepositoryInterface;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Util\ClassUtils;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\Product;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Updater\Setter\FieldSetterInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\NotGrantedDataMergerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class NotGrantedCategoryMergerSpec extends ObjectBehavior
{
    function let(
        AuthorizationCheckerInterface $authorizationChecker,
        FieldSetterInterface $categorySetter
    ) {
        $this->beConstructedWith($authorizationChecker, $categorySetter);
    }

    function it_implements_a_not_granted_data_merger_interface()
    {
        $this->shouldImplement(NotGrantedDataMergerInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\Catalog\Security\Merger\NotGrantedCategoryMerger');
    }

    function it_merges_not_granted_categories_and_removed_a_granted_category(
        $authorizationChecker,
        $categorySetter,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        CategoryInterface $categoryA,
        CategoryInterface $categoryB,
        CategoryInterface $categoryC
    ) {
        $fullProduct->getCategories()->willReturn([$categoryA, $categoryB, $categoryC]);
        $filteredProduct->getCategoryCodes()->willReturn(['category_b']);

        $categoryA->getCode()->willReturn('category_a');

        $authorizationChecker->isGranted([Attributes::VIEW_ITEMS], $categoryA)->willReturn(false);
        $authorizationChecker->isGranted([Attributes::VIEW_ITEMS], $categoryB)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::VIEW_ITEMS], $categoryC)->willReturn(true);

        $categorySetter->setFieldData($fullProduct, 'categories', ['category_b', 'category_a'])->shouldBeCalled();

        $this->merge($filteredProduct, $fullProduct)->shouldReturn($fullProduct);
    }

    function it_merges_not_granted_categories_and_add_a_granted_category(
        $authorizationChecker,
        $categorySetter,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        CategoryInterface $categoryA
    ) {
        $fullProduct->getCategories()->willReturn([$categoryA]);
        $filteredProduct->getCategoryCodes()->willReturn(['category_b']);

        $categoryA->getCode()->willReturn('category_a');

        $authorizationChecker->isGranted([Attributes::VIEW_ITEMS], $categoryA)->willReturn(false);

        $categorySetter->setFieldData($fullProduct, 'categories', ['category_b', 'category_a'])->shouldBeCalled();

        $this->merge($filteredProduct, $fullProduct)->shouldReturn($fullProduct);
    }

    function it_add_categories_on_a_new_product(
        $categorySetter,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct
    ) {
        $fullProduct->getCategories()->willReturn([]);
        $filteredProduct->getCategoryCodes()->willReturn(['category_b']);

        $categorySetter->setFieldData($fullProduct, 'categories', ['category_b'])->shouldBeCalled();

        $this->merge($filteredProduct, $fullProduct)->shouldReturn($fullProduct);
    }

    function it_removes_all_granted_categories(
        $authorizationChecker,
        $categorySetter,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        CategoryInterface $categoryA,
        CategoryInterface $categoryB,
        CategoryInterface $categoryC
    ) {
        $fullProduct->getCategories()->willReturn([$categoryA, $categoryB, $categoryC]);
        $filteredProduct->getCategoryCodes()->willReturn([]);

        $categoryA->getCode()->willReturn('category_a');

        $authorizationChecker->isGranted([Attributes::VIEW_ITEMS], $categoryA)->willReturn(false);
        $authorizationChecker->isGranted([Attributes::VIEW_ITEMS], $categoryB)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::VIEW_ITEMS], $categoryC)->willReturn(true);

        $categorySetter->setFieldData($fullProduct, 'categories', ['category_a'])->shouldBeCalled();

        $this->merge($filteredProduct, $fullProduct)->shouldReturn($fullProduct);
    }

    function it_throws_an_exception_if_filtered_subject_is_not_a_product()
    {
        $this->shouldThrow(InvalidObjectException::objectExpected(ClassUtils::getClass(new \stdClass()), CategoryAwareInterface::class))
            ->during('merge', [new \stdClass(), new Product()]);
    }

    function it_throws_an_exception_if_full_subject_is_not_a_product()
    {
        $this->shouldThrow(InvalidObjectException::objectExpected(ClassUtils::getClass(new \stdClass()), CategoryAwareInterface::class))
            ->during('merge', [new Product(), new \stdClass()]);
    }
}
