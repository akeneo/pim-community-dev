<?php

namespace Specification\Akeneo\Pim\Permission\Component\Merger;

use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FieldSetterInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Merger\NotGrantedCategoryMerger;
use Akeneo\Pim\Permission\Component\NotGrantedDataMergerInterface;
use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use PhpSpec\ObjectBehavior;
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
        $this->shouldHaveType(NotGrantedCategoryMerger::class);
    }

    function it_merges_not_granted_categories_and_removed_a_granted_category(
        AuthorizationCheckerInterface $authorizationChecker,
        FieldSetterInterface $categorySetter,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct
    ) {
        $categoryA = $this->buildCategory('category_a');
        $categoryB = $this->buildCategory('category_b');
        $categoryC = $this->buildCategory('category_c');
        $fullProduct->getCategoriesForVariation()->willReturn(new ArrayCollection([$categoryA, $categoryB, $categoryC]));
        $filteredProduct->getCategoriesForVariation()->willReturn(new ArrayCollection([$categoryB]));

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryA)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryB)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryC)->willReturn(true);

        $categorySetter->setFieldData($fullProduct, 'categories', ['category_b', 'category_a'])->shouldBeCalled();

        $this->merge($filteredProduct, $fullProduct)->shouldReturn($fullProduct);
    }

    function it_merges_not_granted_categories_and_removed_a_granted_category_on_product_model(
        AuthorizationCheckerInterface $authorizationChecker,
        FieldSetterInterface $categorySetter,
        ProductModelInterface $filteredProductModel,
        ProductModelInterface $fullProductModel
    ) {
        $categoryA = $this->buildCategory('category_a');
        $categoryB = $this->buildCategory('category_b');
        $categoryC = $this->buildCategory('category_c');
        $fullProductModel->getCategoriesForCurrentLevel()->willReturn(new ArrayCollection([$categoryA, $categoryB, $categoryC]));
        $filteredProductModel->getCategoriesForCurrentLevel()->willReturn(new ArrayCollection([$categoryB]));

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryA)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryB)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryC)->willReturn(true);

        $categorySetter->setFieldData($fullProductModel, 'categories', ['category_b', 'category_a'])->shouldBeCalled();

        $this->merge($filteredProductModel, $fullProductModel)->shouldReturn($fullProductModel);
    }

    function it_merges_not_granted_categories_and_removed_a_granted_category_on_category_aware_entity(
        AuthorizationCheckerInterface $authorizationChecker,
        FieldSetterInterface $categorySetter,
        CategoryAwareInterface $filteredEntity,
        CategoryAwareInterface $fullEntity
    ) {
        $categoryA = $this->buildCategory('category_a');
        $categoryB = $this->buildCategory('category_b');
        $categoryC = $this->buildCategory('category_c');
        $fullEntity->getCategories()->willReturn(new ArrayCollection([$categoryA, $categoryB, $categoryC]));
        $filteredEntity->getCategories()->willReturn(new ArrayCollection([$categoryB]));

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryA)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryB)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryC)->willReturn(true);

        $categorySetter->setFieldData($fullEntity, 'categories', ['category_b', 'category_a'])->shouldBeCalled();

        $this->merge($filteredEntity, $fullEntity)->shouldReturn($fullEntity);
    }

    function it_merges_not_granted_categories_and_add_a_granted_category(
        AuthorizationCheckerInterface $authorizationChecker,
        FieldSetterInterface $categorySetter,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct
    ) {
        $categoryA = $this->buildCategory('category_a');
        $categoryB = $this->buildCategory('category_b');
        $fullProduct->getCategoriesForVariation()->willReturn(new ArrayCollection([$categoryA]));
        $filteredProduct->getCategoriesForVariation()->willReturn(new ArrayCollection([$categoryB]));

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryA)->willReturn(false);

        $categorySetter->setFieldData($fullProduct, 'categories', ['category_b', 'category_a'])->shouldBeCalled();

        $this->merge($filteredProduct, $fullProduct)->shouldReturn($fullProduct);
    }

    function it_add_categories_on_a_new_product(
        FieldSetterInterface $categorySetter,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct
    ) {
        $categoryB = $this->buildCategory('category_b');
        $fullProduct->getCategoriesForVariation()->willReturn(new ArrayCollection([]));
        $filteredProduct->getCategoriesForVariation()->willReturn(new ArrayCollection([$categoryB]));

        $categorySetter->setFieldData($fullProduct, 'categories', ['category_b'])->shouldBeCalled();

        $this->merge($filteredProduct, $fullProduct)->shouldReturn($fullProduct);
    }

    function it_removes_all_granted_categories(
        AuthorizationCheckerInterface $authorizationChecker,
        FieldSetterInterface $categorySetter,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct
    ) {
        $categoryA = $this->buildCategory('category_a');
        $categoryB = $this->buildCategory('category_b');
        $categoryC = $this->buildCategory('category_c');
        $fullProduct->getCategoriesForVariation()->willReturn(new ArrayCollection([$categoryA, $categoryB, $categoryC]));
        $filteredProduct->getCategoriesForVariation()->willReturn(new ArrayCollection([]));

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryA)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryB)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryC)->willReturn(true);

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

    private function buildCategory(string $code): Category
    {
        $category = new Category();
        $category->setCode($code);

        return $category;
    }
}
