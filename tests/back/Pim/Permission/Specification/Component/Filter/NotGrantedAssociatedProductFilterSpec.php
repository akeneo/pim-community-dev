<?php

namespace Specification\Akeneo\Pim\Permission\Component\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Query\ItemCategoryAccessQuery;
use Akeneo\Pim\Permission\Component\Filter\NotGrantedAssociatedProductFilter;
use Akeneo\Pim\Permission\Component\NotGrantedDataFilterInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\UserManagement\Component\Model\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Util\ClassUtils;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class NotGrantedAssociatedProductFilterSpec extends ObjectBehavior
{
    function let(
        AuthorizationCheckerInterface $authorizationChecker,
        ItemCategoryAccessQuery $productCategoryAccessQuery,
        ItemCategoryAccessQuery $productModelCategoryAccessQuery,
        TokenStorageInterface $tokenStorage
    ) {
        $this->beConstructedWith($authorizationChecker, $productCategoryAccessQuery, $productModelCategoryAccessQuery, $tokenStorage);
    }

    function it_implements_a_filter_interface()
    {
        $this->shouldImplement(NotGrantedDataFilterInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NotGrantedAssociatedProductFilter::class);
    }

    function it_removes_not_granted_associated_products_from_a_product(
        $productCategoryAccessQuery,
        $productModelCategoryAccessQuery,
        $tokenStorage,
        ProductInterface $product,
        ProductInterface $associatedProduct1,
        ProductInterface $associatedProduct2,
        ProductInterface $associatedProduct3,
        ProductModelInterface $associatedProductModel1,
        ProductModelInterface $associatedProductModel2,
        AssociationInterface $associationXSELL,
        AssociationTypeInterface $associationTypeXSELL,
        ArrayCollection $associations,
        Collection $associatedProducts,
        Collection $associatedProductModels,
        \ArrayIterator $iterator,
        \ArrayIterator $iteratorProducts,
        \ArrayIterator $iteratorProductModels,
        TokenInterface $token
    ) {
        $user = new User();
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $associatedProduct1->getId()->willReturn(1);
        $associatedProduct2->getId()->willReturn(2);
        $associatedProduct3->getId()->willReturn(3);
        $associatedProductModel1->getId()->willReturn(10);
        $associatedProductModel2->getId()->willReturn(11);

        $product->getAssociations()->willReturn($associations);
        $associations->getIterator()->willReturn($iterator);
        $iterator->rewind()->shouldBeCalled();
        $iterator->valid()->willReturn(true, false);
        $iterator->current()->willReturn($associationXSELL);
        $iterator->next()->shouldBeCalled();

        $associationXSELL->getProducts()->willReturn($associatedProducts);
        $associationXSELL->getAssociationType()->willReturn($associationTypeXSELL);
        $associationTypeXSELL->getCode()->willReturn('xsell');
        $associatedProducts->getIterator()->willReturn($iteratorProducts);
        $associatedProducts->toArray()->willReturn([$associatedProduct1, $associatedProduct2, $associatedProduct3]);
        $iteratorProducts->rewind()->shouldBeCalled();
        $iteratorProducts->valid()->willReturn(true, true, true, false);
        $iteratorProducts->current()->willReturn($associatedProduct1, $associatedProduct2, $associatedProduct3);
        $iteratorProducts->next()->shouldBeCalled();

        $associationXSELL->getProductModels()->willReturn($associatedProductModels);
        $associatedProductModels->getIterator()->willReturn($iteratorProductModels);
        $associatedProductModels->toArray()->willReturn([$associatedProductModel1, $associatedProductModel2]);
        $iteratorProductModels->rewind()->shouldBeCalled();
        $iteratorProductModels->valid()->willReturn(true, true, false);
        $iteratorProductModels->current()->willReturn($associatedProductModel1, $associatedProductModel2);
        $iteratorProductModels->next()->shouldBeCalled();

        $productCategoryAccessQuery->getGrantedItemIds([$associatedProduct1, $associatedProduct2, $associatedProduct3], $user)
            ->willReturn([2 => 2, 3 => 3]);

        $product->removeAssociatedProduct($associatedProduct1, 'xsell')->shouldBeCalled();
        $product->removeAssociatedProduct($associatedProduct2, Argument::any())->shouldNotBeCalled();
        $product->removeAssociatedProduct($associatedProduct3, Argument::any())->shouldNotBeCalled();

        $productModelCategoryAccessQuery->getGrantedItemIds([$associatedProductModel1, $associatedProductModel2], $user)
            ->willReturn([10 => 10]);
        $product->removeAssociatedProductModel($associatedProductModel1, Argument::any())->shouldNotBeCalled();
        $product->removeAssociatedProductModel($associatedProductModel2, 'xsell')->shouldBeCalled();

        $this->filter($product)->shouldReturnAnInstanceOf(ProductInterface::class);
    }

    function it_throws_an_exception_if_subject_is_not_a_product()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                ClassUtils::getClass(new \stdClass()), EntityWithAssociationsInterface::class)
        )
            ->during('filter', [new \stdClass()]);
    }
}
