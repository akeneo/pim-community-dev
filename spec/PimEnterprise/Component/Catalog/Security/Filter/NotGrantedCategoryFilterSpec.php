<?php

namespace spec\PimEnterprise\Component\Catalog\Security\Filter;

use Akeneo\Component\Classification\CategoryAwareInterface;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException;
use PimEnterprise\Component\Security\NotGrantedDataFilterInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class NotGrantedCategoryFilterSpec extends ObjectBehavior
{
    function let(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->beConstructedWith($authorizationChecker);
    }

    function it_implements_a_filter_interface()
    {
        $this->shouldImplement(NotGrantedDataFilterInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\Catalog\Security\Filter\NotGrantedCategoryFilter');
    }

    function it_removes_not_granted_categories_from_a_product(
        $authorizationChecker,
        ProductInterface $product,
        ArrayCollection $categories,
        \ArrayIterator $iterator,
        CategoryInterface $categoryA,
        CategoryInterface $categoryB
    ) {
        $product->getCategories()->willReturn($categories);
        $categories->count()->willReturn(2);

        $categories->getIterator()->willReturn($iterator);
        $iterator->rewind()->shouldBeCalled();
        $iterator->valid()->willReturn(true, true, false);
        $iterator->current()->willReturn($categoryA, $categoryB);
        $iterator->key()->willReturn(1, 2);
        $iterator->next()->shouldBeCalled();

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryA)->willReturn(true);
        $categories->remove(1)->shouldNotBeCalled();

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryB)->willReturn(false);
        $categories->remove(2)->shouldBeCalled();

        $this->filter($product)->shouldReturn($product);
    }

    function it_removes_not_granted_categories_from_a_product_model(
        $authorizationChecker,
        ProductModelInterface $productModel,
        ArrayCollection $categories,
        \ArrayIterator $iterator,
        CategoryInterface $categoryA,
        CategoryInterface $categoryB
    ) {
        $productModel->getCategories()->willReturn($categories);
        $categories->count()->willReturn(2);

        $categories->getIterator()->willReturn($iterator);
        $iterator->rewind()->shouldBeCalled();
        $iterator->valid()->willReturn(true, true, false);
        $iterator->current()->willReturn($categoryA, $categoryB);
        $iterator->key()->willReturn(1, 2);
        $iterator->next()->shouldBeCalled();

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryA)->willReturn(true);
        $categories->remove(1)->shouldNotBeCalled();

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryB)->willReturn(false);
        $categories->remove(2)->shouldBeCalled();

        $this->filter($productModel)->shouldReturn($productModel);
    }

    function it_throws_an_exception_if_all_categories_have_been_removed_and_make_product_not_viewable(
        $authorizationChecker,
        ProductInterface $product,
        ArrayCollection $categories,
        \ArrayIterator $iterator,
        CategoryInterface $categoryA,
        CategoryInterface $categoryB
    ) {
        $product->getIdentifier()->willReturn('product_a');
        $product->getCategories()->willReturn($categories);
        $categories->count()->willReturn(2, 2);

        $categories->getIterator()->willReturn($iterator);
        $iterator->rewind()->shouldBeCalled();
        $iterator->valid()->willReturn(true, true, false);
        $iterator->key()->willReturn(1, 2);
        $iterator->current()->willReturn($categoryA, $categoryB);
        $iterator->next()->shouldBeCalled();

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryB)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryA)->willReturn(false);

        $this->shouldThrow(
            new ResourceAccessDeniedException(
                $product->getWrappedObject(),
                'You can neither view, nor update, nor delete the product "product_a", as it is only categorized in categories on which you do not have a view permission.'
            )
        )->during('filter', [$product]);
    }

    function it_throws_an_exception_if_all_categories_have_been_removed_and_make_product_model_not_viewable(
        $authorizationChecker,
        ProductModelInterface $productModel,
        ArrayCollection $categories,
        \ArrayIterator $iterator,
        CategoryInterface $categoryA,
        CategoryInterface $categoryB
    ) {
        $productModel->getCode()->willReturn('product_model_a');
        $productModel->getCategories()->willReturn($categories);
        $categories->count()->willReturn(2, 2);

        $categories->getIterator()->willReturn($iterator);
        $iterator->rewind()->shouldBeCalled();
        $iterator->valid()->willReturn(true, true, false);
        $iterator->key()->willReturn(1, 2);
        $iterator->current()->willReturn($categoryA, $categoryB);
        $iterator->next()->shouldBeCalled();

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryB)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryA)->willReturn(false);

        $this->shouldThrow(
            new ResourceAccessDeniedException(
                $productModel->getWrappedObject(),
                'You can neither view, nor update, nor delete the product model "product_model_a", as it is only categorized in categories on which you do not have a view permission.'
            )
        )->during('filter', [$productModel]);
    }

    function it_throws_an_exception_if_all_categories_have_been_removed_and_make_category_aware_entity_not_viewable(
        $authorizationChecker,
        CategoryAwareInterface $categoryAwareEntity,
        ArrayCollection $categories,
        \ArrayIterator $iterator,
        CategoryInterface $categoryA,
        CategoryInterface $categoryB
    ) {
        $categoryAwareEntity->getCategories()->willReturn($categories);
        $categories->count()->willReturn(2, 2);

        $categories->getIterator()->willReturn($iterator);
        $iterator->rewind()->shouldBeCalled();
        $iterator->valid()->willReturn(true, true, false);
        $iterator->key()->willReturn(1, 2);
        $iterator->current()->willReturn($categoryA, $categoryB);
        $iterator->next()->shouldBeCalled();

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryB)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $categoryA)->willReturn(false);

        $this->shouldThrow(
            new ResourceAccessDeniedException(
                $categoryAwareEntity->getWrappedObject(),
                'You can neither view, nor update, nor delete this entity, as it is only categorized in categories on which you do not have a view permission.'
            )
        )->during('filter', [$categoryAwareEntity]);
    }

    function it_throws_an_exception_if_subject_is_not_a_category_aware_entity()
    {
        $this->shouldThrow(InvalidObjectException::objectExpected(ClassUtils::getClass(new \stdClass()), CategoryAwareInterface::class))
            ->during('filter', [new \stdClass()]);
    }
}
