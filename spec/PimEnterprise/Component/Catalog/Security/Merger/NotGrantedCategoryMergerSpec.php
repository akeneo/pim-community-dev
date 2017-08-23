<?php

namespace spec\PimEnterprise\Component\Catalog\Security\Merger;

use Akeneo\Component\Classification\Repository\ItemCategoryRepositoryInterface;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Util\ClassUtils;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\NotGrantedDataMergerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class NotGrantedCategoryMergerSpec extends ObjectBehavior
{
    function let(
        AuthorizationCheckerInterface $authorizationChecker,
        ItemCategoryRepositoryInterface $productCategoryRepository
    ) {
        $this->beConstructedWith($authorizationChecker, $productCategoryRepository);
    }

    function it_implements_a_not_granted_data_merger_interface()
    {
        $this->shouldImplement(NotGrantedDataMergerInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\Catalog\Security\Merger\NotGrantedCategoryMerger');
    }

    function it_merges_not_granted_categories_in_product(
        $authorizationChecker,
        $productCategoryRepository,
        ProductInterface $product,
        CategoryInterface $categoryA,
        CategoryInterface $categoryB,
        CategoryInterface $categoryC
    ) {
        $product->getId()->willReturn(1);
        $productCategoryRepository->findCategoriesItem($product)->willReturn([$categoryA, $categoryB, $categoryC]);

        $authorizationChecker->isGranted([Attributes::VIEW_ITEMS], $categoryA)->willReturn(false);
        $authorizationChecker->isGranted([Attributes::VIEW_ITEMS], $categoryB)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::VIEW_ITEMS], $categoryC)->willReturn(true);

        $product->addCategory($categoryA)->shouldBeCalled();
        $product->addCategory($categoryB)->shouldNotBeCalled();
        $product->addCategory($categoryC)->shouldNotBeCalled();

        $this->merge($product)->shouldReturn(null);
    }

    function it_throws_an_exception_if_subject_is_not_a_product()
    {
        $this->shouldThrow(InvalidObjectException::objectExpected(ClassUtils::getClass(new \stdClass()), ProductInterface::class))
            ->during('merge', [new \stdClass()]);
    }
}
