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

        $product->setCategories($categories)->shouldBeCalled();

        $this->filter($product)->shouldReturnAnInstanceOf(ProductInterface::class);
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

        $productModel->setCategories($categories)->shouldBeCalled();

        $this->filter($productModel)->shouldReturnAnInstanceOf(ProductModelInterface::class);
    }

    function it_throws_an_exception_if_subject_is_not_a_category_aware_entity()
    {
        $this->shouldThrow(InvalidObjectException::objectExpected(ClassUtils::getClass(new \stdClass()), CategoryAwareInterface::class))
            ->during('filter', [new \stdClass()]);
    }
}
