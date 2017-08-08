<?php

namespace spec\PimEnterprise\Component\Catalog\Security\Merger;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Util\ClassUtils;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\NotGrantedDataMergerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class NotGrantedAssociatedProductMergerSpec extends ObjectBehavior
{
    function let(
        AuthorizationCheckerInterface $authorizationChecker,
        ProductRepositoryInterface $productRepository
    ) {
        $this->beConstructedWith($authorizationChecker, $productRepository);
    }

    function it_implements_a_not_granted_data_merger_interface()
    {
        $this->shouldImplement(NotGrantedDataMergerInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\Catalog\Security\Merger\NotGrantedAssociatedProductMerger');
    }

    function it_merges_not_granted_associated_products_in_product(
        $productRepository,
        $authorizationChecker,
        ProductInterface $product,
        ProductInterface $productB,
        ProductInterface $productC,
        AssociationInterface $XSELL
    ) {
        $productRepository->getAssociatedProductIds($product)->willReturn([
            ['product_identifier' => 'product_b', 'association_type_code' => 'X_SELL'],
            ['product_identifier' => 'product_c', 'association_type_code' => 'X_SELL']
        ]);

        $productRepository->findOneByIdentifier('product_b')->willReturn($productB);
        $productRepository->findOneByIdentifier('product_c')->willReturn($productC);

        $authorizationChecker->isGranted([Attributes::VIEW], $productB)->willReturn(false);
        $authorizationChecker->isGranted([Attributes::VIEW], $productC)->willReturn(true);

        $product->getAssociationForTypeCode('X_SELL')->willReturn($XSELL);
        $XSELL->addProduct($productB)->shouldBeCalled();
        $XSELL->addProduct($productC)->shouldNotBeCalled();

        $this->merge($product)->shouldReturn(null);
    }

    function it_throws_an_exception_if_subject_is_not_a_product()
    {
        $this->shouldThrow(InvalidObjectException::objectExpected(ClassUtils::getClass(new \stdClass()), ProductInterface::class))
            ->during('merge', [new \stdClass()]);
    }
}
