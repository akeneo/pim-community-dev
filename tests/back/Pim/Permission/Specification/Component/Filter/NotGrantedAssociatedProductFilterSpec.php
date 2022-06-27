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
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class NotGrantedAssociatedProductFilterSpec extends ObjectBehavior
{
    function let(
        ItemCategoryAccessQuery $productCategoryAccessQuery,
        ItemCategoryAccessQuery $productModelCategoryAccessQuery,
        TokenStorageInterface $tokenStorage
    ) {
        $this->beConstructedWith($productCategoryAccessQuery, $productModelCategoryAccessQuery, $tokenStorage);
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
        Collection $associatedProductsAndPublishedProducts,
        Collection $associatedProducts,
        Collection $associatedPublishedProducts,
        Collection $associatedProductModels,
        \ArrayIterator $iterator,
        \ArrayIterator $iteratorProducts,
        \ArrayIterator $iteratorProductModels,
        TokenInterface $token
    ) {
        $user = new User();
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $associatedProduct1->getUuid()->willReturn(Uuid::fromString('b816e413-253e-4e63-ae1e-562deb93558f'));
        $associatedProduct2->getUuid()->willReturn(Uuid::fromString('6fc5f000-2c00-4eac-899b-a85e41d6a42d'));
        $associatedProduct3->getUuid()->willReturn(Uuid::fromString('aab1fcbf-bacb-430c-8a90-b9d34db2d676'));
        $associatedProductModel1->getId()->willReturn(10);
        $associatedProductModel2->getId()->willReturn(11);

        $product->getAssociations()->willReturn($associations);
        $associations->getIterator()->willReturn($iterator);
        $iterator->rewind()->shouldBeCalled();
        $iterator->valid()->willReturn(true, false);
        $iterator->current()->willReturn($associationXSELL);
        $iterator->next()->shouldBeCalled();

        $associationXSELL->getProducts()->willReturn($associatedProductsAndPublishedProducts);
        $associationXSELL->getAssociationType()->willReturn($associationTypeXSELL);
        $associationTypeXSELL->getCode()->willReturn('xsell');
        $associatedProductsAndPublishedProducts->filter(Argument::any())->willReturn($associatedProducts, $associatedPublishedProducts);
        $associatedProducts->count()->willReturn(3);
        $associatedPublishedProducts->count()->willReturn(0);
        $associatedProducts->getIterator()->willReturn($iteratorProducts);
        $associatedProducts->toArray()->willReturn([$associatedProduct1, $associatedProduct2, $associatedProduct3]);
        $associatedProduct1->getUuid()->willReturn(Uuid::fromString('320aa527-a5b3-4648-ad57-603d4f27a0e9'));
        $associatedProduct2->getUuid()->willReturn(Uuid::fromString('6fc5f000-2c00-4eac-899b-a85e41d6a42d'));
        $associatedProduct3->getUuid()->willReturn(Uuid::fromString('aab1fcbf-bacb-430c-8a90-b9d34db2d676'));
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

        $productCategoryAccessQuery->getGrantedProductUuids([$associatedProduct1, $associatedProduct2, $associatedProduct3], $user)
            ->willReturn(['6fc5f000-2c00-4eac-899b-a85e41d6a42d', 'aab1fcbf-bacb-430c-8a90-b9d34db2d676']);

        $product->removeAssociatedProduct($associatedProduct1, 'xsell')->shouldBeCalled();
        $product->removeAssociatedProduct($associatedProduct2, Argument::any())->shouldNotBeCalled();
        $product->removeAssociatedProduct($associatedProduct3, Argument::any())->shouldNotBeCalled();

        $productModelCategoryAccessQuery->getGrantedItemIds([$associatedProductModel1, $associatedProductModel2], $user)
            ->willReturn([10 => 10]);
        $product->removeAssociatedProductModel($associatedProductModel1, Argument::any())->shouldNotBeCalled();
        $product->removeAssociatedProductModel($associatedProductModel2, 'xsell')->shouldBeCalled();

        $this->filter($product)->shouldReturnAnInstanceOf(ProductInterface::class);
    }

    function it_removes_not_granted_associated_published_products_from_a_product(
        $productCategoryAccessQuery,
        $productModelCategoryAccessQuery,
        $tokenStorage,
        ProductInterface $product,
        ProductInterface $associatedPublished1,
        ProductInterface $associatedPublished2,
        ProductInterface $associatedPublished3,
        ProductModelInterface $associatedProductModel1,
        ProductModelInterface $associatedProductModel2,
        AssociationInterface $associationXSELL,
        AssociationTypeInterface $associationTypeXSELL,
        ArrayCollection $associations,
        Collection $associatedProductsAndPublishedProducts,
        Collection $associatedProducts,
        Collection $associatedPublishedProducts,
        Collection $associatedProductModels,
        \ArrayIterator $iterator,
        \ArrayIterator $iteratorPublishedProducts,
        \ArrayIterator $iteratorProductModels,
        TokenInterface $token
    ) {
        $user = new User();
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $associatedPublished1->getUuid()->willReturn(Uuid::fromString('b816e413-253e-4e63-ae1e-562deb93558f'));
        $associatedPublished2->getUuid()->willReturn(Uuid::fromString('6fc5f000-2c00-4eac-899b-a85e41d6a42d'));
        $associatedPublished3->getUuid()->willReturn(Uuid::fromString('aab1fcbf-bacb-430c-8a90-b9d34db2d676'));
        $associatedProductModel1->getId()->willReturn(10);
        $associatedProductModel2->getId()->willReturn(11);

        $product->getAssociations()->willReturn($associations);
        $associations->getIterator()->willReturn($iterator);
        $iterator->rewind()->shouldBeCalled();
        $iterator->valid()->willReturn(true, false);
        $iterator->current()->willReturn($associationXSELL);
        $iterator->next()->shouldBeCalled();

        $associationXSELL->getProducts()->willReturn($associatedProductsAndPublishedProducts);
        $associationXSELL->getAssociationType()->willReturn($associationTypeXSELL);
        $associationTypeXSELL->getCode()->willReturn('xsell');
        $associatedProductsAndPublishedProducts->filter(Argument::any())->willReturn($associatedProducts, $associatedPublishedProducts);
        $associatedProducts->count()->willReturn(0);
        $associatedPublishedProducts->count()->willReturn(3);
        $associatedPublishedProducts->getIterator()->willReturn($iteratorPublishedProducts);
        $associatedPublishedProducts->toArray()->willReturn([$associatedPublished1, $associatedPublished2, $associatedPublished3]);
        $associatedPublished1->getId()->willReturn(10);
        $associatedPublished2->getId()->willReturn(11);
        $associatedPublished3->getId()->willReturn(12);
        $iteratorPublishedProducts->rewind()->shouldBeCalled();
        $iteratorPublishedProducts->valid()->willReturn(true, true, true, false);
        $iteratorPublishedProducts->current()->willReturn($associatedPublished1, $associatedPublished2, $associatedPublished3);
        $iteratorPublishedProducts->next()->shouldBeCalled();

        $associationXSELL->getProductModels()->willReturn($associatedProductModels);
        $associatedProductModels->getIterator()->willReturn($iteratorProductModels);
        $associatedProductModels->toArray()->willReturn([$associatedProductModel1, $associatedProductModel2]);
        $iteratorProductModels->rewind()->shouldBeCalled();
        $iteratorProductModels->valid()->willReturn(true, true, false);
        $iteratorProductModels->current()->willReturn($associatedProductModel1, $associatedProductModel2);
        $iteratorProductModels->next()->shouldBeCalled();

        $productCategoryAccessQuery->getGrantedItemIds([$associatedPublished1, $associatedPublished2, $associatedPublished3], $user)
            ->willReturn([11 => 11, 12 => 12]);

        $product->removeAssociatedProduct($associatedPublished1, 'xsell')->shouldBeCalled();
        $product->removeAssociatedProduct($associatedPublished2, Argument::any())->shouldNotBeCalled();
        $product->removeAssociatedProduct($associatedPublished3, Argument::any())->shouldNotBeCalled();

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
