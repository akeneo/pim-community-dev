<?php

namespace Specification\Akeneo\Pim\Permission\Component\Remover;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Exception\ResourceAccessDeniedException;
use Akeneo\Pim\Permission\Component\Remover\ProductRemover;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProductRemoverSpec extends ObjectBehavior
{
    function let(
        RemoverInterface $remover,
        BulkRemoverInterface $bulkRemover,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductRepositoryInterface $productRepository
    ) {
        $this->beConstructedWith($remover, $bulkRemover, $authorizationChecker, $productRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductRemover::class);
    }

    function it_removes_a_product(
        ProductInterface $product,
        $remover,
        $authorizationChecker,
        $productRepository,
        ProductInterface $fullProduct
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(true);

        $options = ['option' => 'foo'];

        $uuid = Uuid::uuid4();
        $product->getUuid()->willReturn($uuid);
        $productRepository->find($uuid)->willReturn($fullProduct);

        $remover->remove($fullProduct, $options)->shouldBeCalled();
        $this->remove($product, $options);
    }

    function it_removes_a_list_of_products(
        ProductInterface $firstProduct,
        ProductInterface $secondProduct,
        $bulkRemover,
        $authorizationChecker,
        $productRepository,
        ProductInterface $fullFirstProduct,
        ProductInterface $fullSecondProduct
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $firstProduct)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::OWN, $secondProduct)->willReturn(true);

        $firstUuid = Uuid::uuid4();
        $firstProduct->getUuid()->willReturn($firstUuid);
        $productRepository->find($firstUuid)->willReturn($fullFirstProduct);

        $secondUuid = Uuid::uuid4();
        $secondProduct->getUuid()->willReturn($secondUuid);
        $productRepository->find($secondUuid)->willReturn($fullSecondProduct);

        $products = [$fullFirstProduct, $fullSecondProduct];
        $options = ['option' => 'foo'];

        $bulkRemover->removeAll($products, $options)->shouldBeCalled();
        $this->removeAll([$firstProduct, $secondProduct], $options);
    }

    function it_throws_an_exception_when_the_object_to_remove_is_not_a_product()
    {
        $invalidProduct = new \stdClass();

        $this->shouldThrow(InvalidObjectException::objectExpected(\stdClass::class,
            ProductInterface::class
        ))
            ->during('remove', [$invalidProduct]);
    }

    function it_throws_an_exception_when_one_of_the_objects_to_remove_is_not_a_product(
        ProductInterface $firstProduct,
        UuidInterface $uuid,
        $authorizationChecker
    ) {
        $secondProduct = new \stdClass();
        $products = [$firstProduct, $secondProduct];
        $firstProduct->getUuid()->willReturn($uuid);

        $authorizationChecker->isGranted(Attributes::OWN, $firstProduct)->willReturn(true);

        $this->shouldThrow(InvalidObjectException::objectExpected(\stdClass::class,
            ProductInterface::class
        ))
            ->during('removeAll', [$products]);
    }

    function it_throws_an_exception_when_the_user_is_not_authorized_to_remove_the_product(ProductInterface $product, $authorizationChecker)
    {
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);

        $this->shouldThrow(
            new ResourceAccessDeniedException(
                $product->getWrappedObject(),
                'You can delete a product only if it is classified in at least one category on which you have an own permission.'
            )
        )->during('remove', [$product]);
    }

    function it_throws_an_exception_when_the_user_is_not_authorized_to_remove_one_of_the_products(
        ProductInterface $firstProduct,
        UuidInterface $firstProductUuid,
        ProductInterface $secondProduct,
        $authorizationChecker
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $firstProduct)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::OWN, $secondProduct)->willReturn(false);
        $firstProduct->getUuid()->willReturn($firstProductUuid);

        $this->shouldThrow(
            new ResourceAccessDeniedException(
                $secondProduct->getWrappedObject(),
                'You can delete a product only if it is classified in at least one category on which you have an own permission.'
            )
        )->during('removeAll', [[$firstProduct, $secondProduct]]);
    }
}
