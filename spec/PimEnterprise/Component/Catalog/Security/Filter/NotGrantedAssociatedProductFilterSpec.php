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
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\NotGrantedDataFilterInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class NotGrantedAssociatedProductFilterSpec extends ObjectBehavior
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
        $this->shouldHaveType('PimEnterprise\Component\Catalog\Security\Filter\NotGrantedAssociatedProductFilter');
    }

    function it_removes_not_granted_associated_products_from_a_product(
        $authorizationChecker,
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
        \ArrayIterator $iteratorProductModels
    ) {
        $product->getAssociations()->willReturn($associations);
        $associations->getIterator()->willReturn($iterator);
        $iterator->rewind()->shouldBeCalled();
        $iterator->valid()->willReturn(true, false);
        $iterator->current()->willReturn($associationXSELL);
        $iterator->next()->shouldBeCalled();

        $associationXSELL->getProducts()->willReturn($associatedProducts);
        $associatedProducts->getIterator()->willReturn($iteratorProducts);
        $iteratorProducts->rewind()->shouldBeCalled();
        $iteratorProducts->valid()->willReturn(true, true, true, false);
        $iteratorProducts->current()->willReturn($associatedProduct1);
        $iteratorProducts->next()->shouldBeCalled();

        $associationXSELL->getProductModels()->willReturn($associatedProductModels);
        $associatedProductModels->getIterator()->willReturn($iteratorProductModels);
        $iteratorProductModels->rewind()->shouldBeCalled();
        $iteratorProductModels->valid()->willReturn(true, false);
        $iteratorProductModels->current()->willReturn($associatedProductModel1);
        $iteratorProductModels->next()->shouldBeCalled();

        $authorizationChecker->isGranted([Attributes::VIEW], $associatedProduct1)->willReturn(false);
        $associatedProducts->removeElement($associatedProduct1)->shouldBeCalled();

        $authorizationChecker->isGranted([Attributes::VIEW], $associatedProduct2)->willReturn(true);
        $associatedProducts->removeElement($associatedProduct2)->shouldNotBeCalled();

        $authorizationChecker->isGranted([Attributes::VIEW], $associatedProduct3)->willReturn(true);
        $associatedProducts->removeElement($associatedProduct3)->shouldNotBeCalled();

        $authorizationChecker->isGranted([Attributes::VIEW], $associatedProductModel1)->willReturn(true);
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
