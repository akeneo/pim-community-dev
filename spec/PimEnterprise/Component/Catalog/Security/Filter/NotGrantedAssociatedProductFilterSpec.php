<?php

namespace spec\PimEnterprise\Component\Catalog\Security\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Component\Security\Exception\ResourceAccessDeniedHttpException;
use PimEnterprise\Component\Security\NotGrantedDataFilterInterface;
use Prophecy\Argument;

class NotGrantedAssociatedProductFilterSpec extends ObjectBehavior
{
    function let(ProductRepositorySpec $productRepository)
    {
        $this->beConstructedWith($productRepository);
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
        ProductRepositorySpec $productRepository,
        ProductInterface $product,
        ProductInterface $associatedProduct1,
        ProductInterface $associatedProduct2,
        ProductInterface $associatedProduct3,
        AssociationInterface $associationXSELL,
        AssociationInterface $associationUPSELL,
        ArrayCollection $associations,
        \ArrayIterator $iterator,
        ResourceAccessDeniedHttpException $resourceAccessDeniedHttpException
    ) {
        $productRepository->getAssociatedProductIds($product)->willReturn([
            ['association_id' => 1, 'product_id' => 1],
            ['association_id' => 1, 'product_id' => 2],
            ['association_id' => 2, 'product_id' => 3],
        ]);

        $associationXSELL->getId()->willReturn(1);
        $associatedProduct1->getId()->willReturn(1);
        $associatedProduct2->getId()->willReturn(2);
        $associationXSELL->addProduct($associatedProduct1);
        $associationXSELL->addProduct($associatedProduct2);

        $associationUPSELL->getId()->willReturn(2);
        $associatedProduct3->getId()->willReturn(3);
        $associationUPSELL->addProduct($associatedProduct3);

        $associations->getIterator()->willReturn($iterator);
        $iterator->rewind()->shouldBeCalled();
        $iterator->valid()->willReturn(true, true, false);
        $iterator->current()->willReturn($associationXSELL);
        $iterator->next()->shouldBeCalled();

        $iterator->current()->willReturn($associationUPSELL);

        $product->getAssociations()->willReturn($associations);
        $resourceAccessDeniedHttpException->getResource()->willReturn($associatedProduct1);

        $productRepository->find(1)->willThrow($resourceAccessDeniedHttpException->getWrappedObject());
        $associationXSELL->removeProduct($associatedProduct1)->willReturn($associationXSELL);
        $productRepository->find(2)->willReturn($associatedProduct2);
        $associationXSELL->removeProduct($associatedProduct2)->shouldNotBeCalled();
        $productRepository->find(3)->willReturn($associatedProduct3);
        $associationXSELL->removeProduct($associatedProduct3)->shouldNotBeCalled();

        $this->filter($product)->shouldReturn($product);
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
