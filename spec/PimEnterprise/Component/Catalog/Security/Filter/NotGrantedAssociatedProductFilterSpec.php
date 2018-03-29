<?php

namespace spec\PimEnterprise\Component\Catalog\Security\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Util\ClassUtils;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Query\ItemCategoryAccessQuery;
use PimEnterprise\Component\Catalog\Security\Filter\NotGrantedAssociatedProductFilter;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\NotGrantedDataFilterInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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
        AssociationInterface $associationXSELL,
        ArrayCollection $associations,
        Collection $associatedProducts,
        Collection $associatedProductModels,
        \ArrayIterator $iterator,
        \ArrayIterator $iteratorProducts,
        \ArrayIterator $iteratorProductModels,
        TokenInterface $token,
        UserInterface $user
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $associatedProduct1->getId()->willReturn(1);
        $associatedProduct2->getId()->willReturn(2);
        $associatedProduct3->getId()->willReturn(3);
        $associatedProductModel1->getId()->willReturn(1);

        $product->getAssociations()->willReturn($associations);
        $associations->getIterator()->willReturn($iterator);
        $iterator->rewind()->shouldBeCalled();
        $iterator->valid()->willReturn(true, false);
        $iterator->current()->willReturn($associationXSELL);
        $iterator->next()->shouldBeCalled();

        $associationXSELL->getProducts()->willReturn($associatedProducts);
        $associatedProducts->getIterator()->willReturn($iteratorProducts);
        $associatedProducts->toArray()->willReturn([$associatedProduct1, $associatedProduct2, $associatedProduct3]);
        $iteratorProducts->rewind()->shouldBeCalled();
        $iteratorProducts->valid()->willReturn(true, true, true, false);
        $iteratorProducts->current()->willReturn($associatedProduct1, $associatedProduct2, $associatedProduct3);
        $iteratorProducts->next()->shouldBeCalled();

        $associationXSELL->getProductModels()->willReturn($associatedProductModels);
        $associatedProductModels->getIterator()->willReturn($iteratorProductModels);
        $associatedProductModels->toArray()->willReturn([$associatedProductModel1]);
        $iteratorProductModels->rewind()->shouldBeCalled();
        $iteratorProductModels->valid()->willReturn(true, false);
        $iteratorProductModels->current()->willReturn($associatedProductModel1);
        $iteratorProductModels->next()->shouldBeCalled();

        $productCategoryAccessQuery->getGrantedItemIds([$associatedProduct1, $associatedProduct2, $associatedProduct3], $user)
            ->willReturn([2 => 2, 3 => 3]);
        $associatedProducts->removeElement($associatedProduct1)->shouldBeCalled();
        $associatedProducts->removeElement($associatedProduct2)->shouldNotBeCalled();
        $associatedProducts->removeElement($associatedProduct3)->shouldNotBeCalled();

        $productModelCategoryAccessQuery->getGrantedItemIds([$associatedProductModel1], $user)
            ->willReturn([1 => 1]);
        $associatedProductModels->removeElement($associatedProductModel1)->shouldNotBeCalled();

        $associationXSELL->setProducts($associatedProducts)->shouldBeCalled();
        $associationXSELL->setProductModels($associatedProductModels)->shouldBeCalled();
        $product->setAssociations(Argument::type(ArrayCollection::class))->shouldBeCalled();

        $this->filter($product)->shouldReturnAnInstanceOf(ProductInterface::class);
    }

    function it_throws_an_exception_if_subject_is_not_a_product()
    {
        $this->shouldThrow(InvalidObjectException::objectExpected(ClassUtils::getClass(new \stdClass()), ProductInterface::class))
            ->during('filter', [new \stdClass()]);
    }
}

interface ProductRepositorySpec extends ProductRepositoryInterface
{
    public function find($id);
}
