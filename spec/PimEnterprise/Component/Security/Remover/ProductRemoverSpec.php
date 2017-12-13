<?php

namespace spec\PimEnterprise\Component\Security\Remover;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException;
use PimEnterprise\Component\Security\Remover\ProductRemover;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProductRemoverSpec extends ObjectBehavior
{
    function let(
        RemoverInterface $remover,
        BulkRemoverInterface $bulkRemover,
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $productRepository
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

        $product->getIdentifier()->willReturn('code');
        $productRepository->findOneByIdentifier('code')->willReturn($fullProduct);

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

        $firstProduct->getIdentifier()->willReturn('code1');
        $productRepository->findOneByIdentifier('code1')->willReturn($fullFirstProduct);

        $secondProduct->getIdentifier()->willReturn('code2');
        $productRepository->findOneByIdentifier('code2')->willReturn($fullSecondProduct);

        $products = [$fullFirstProduct, $fullSecondProduct];
        $options = ['option' => 'foo'];

        $bulkRemover->removeAll($products, $options)->shouldBeCalled();
        $this->removeAll([$firstProduct, $secondProduct], $options);
    }

    function it_throws_an_exception_when_the_object_to_remove_is_not_a_product()
    {
        $invalidProduct = new \stdClass();

        $this->shouldThrow(InvalidObjectException::objectExpected('stdClass', 'Pim\Component\Catalog\Model\ProductInterface'))
            ->during('remove', [$invalidProduct]);
    }

    function it_throws_an_exception_when_one_of_the_objects_to_remove_is_not_a_product(ProductInterface $firstProduct, $authorizationChecker)
    {
        $secondProduct = new \stdClass();
        $products = [$firstProduct, $secondProduct];

        $authorizationChecker->isGranted(Attributes::OWN, $firstProduct)->willReturn(true);


        $this->shouldThrow(InvalidObjectException::objectExpected('stdClass', 'Pim\Component\Catalog\Model\ProductInterface'))
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
        ProductInterface $secondProduct,
        $authorizationChecker
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $firstProduct)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::OWN, $secondProduct)->willReturn(false);

        $this->shouldThrow(
            new ResourceAccessDeniedException(
                $secondProduct->getWrappedObject(),
                'You can delete a product only if it is classified in at least one category on which you have an own permission.'
            )
        )->during('removeAll', [[$firstProduct, $secondProduct]]);
    }
}
