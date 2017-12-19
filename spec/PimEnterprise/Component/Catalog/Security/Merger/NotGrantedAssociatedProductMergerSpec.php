<?php

namespace spec\PimEnterprise\Component\Catalog\Security\Merger;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Util\ClassUtils;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\Product;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\Updater\Setter\FieldSetterInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\NotGrantedDataMergerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class NotGrantedAssociatedProductMergerSpec extends ObjectBehavior
{
    function let(
        AuthorizationCheckerInterface $authorizationChecker,
        FieldSetterInterface $associationSetter
    ) {
        $this->beConstructedWith($authorizationChecker, $associationSetter);
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
        $authorizationChecker,
        $associationSetter,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        ProductInterface $productB,
        ProductInterface $productC,
        ProductInterface $productD,
        AssociationInterface $XSELLForFilteredProduct,
        AssociationTypeInterface $associationTypeXSELLForFilteredProduct,
        AssociationInterface $XSELLForFullProduct,
        AssociationTypeInterface $associationTypeXSELLForFullProduct
    ) {
        $productB->getIdentifier()->willReturn('product_b');
        $productC->getIdentifier()->willReturn('product_c');
        $productD->getIdentifier()->willReturn('product_d');

        $fullProduct->getAssociations()->willReturn([$XSELLForFullProduct]);
        $XSELLForFullProduct->getAssociationType()->willReturn($associationTypeXSELLForFullProduct);
        $XSELLForFullProduct->getProducts()->willReturn([$productB, $productC]);
        $associationTypeXSELLForFullProduct->getCode()->willReturn('X_SELL');
        $authorizationChecker->isGranted([Attributes::VIEW], $productB)->willReturn(false);
        $authorizationChecker->isGranted([Attributes::VIEW], $productC)->willReturn(true);

        $filteredProduct->getAssociations()->willReturn([$XSELLForFilteredProduct]);
        $XSELLForFilteredProduct->getAssociationType()->willReturn($associationTypeXSELLForFilteredProduct);
        $XSELLForFilteredProduct->getProducts()->willReturn([$productC, $productD]);
        $XSELLForFilteredProduct->getGroups()->willReturn([]);
        $associationTypeXSELLForFilteredProduct->getCode()->willReturn('X_SELL');

        $associationSetter->setFieldData($fullProduct, 'associations', [
            'X_SELL' => ['products' => ['product_b', 'product_c', 'product_d']]
        ])->shouldBeCalled();

        $this->merge($filteredProduct, $fullProduct)->shouldReturn($fullProduct);
    }

    function it_merges_not_granted_associated_products_with_new_association_type_in_product(
        $authorizationChecker,
        $associationSetter,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        ProductInterface $productB,
        ProductInterface $productC,
        ProductInterface $productD,
        AssociationInterface $UPSELLForFilteredProduct,
        AssociationTypeInterface $associationTypeUPSELLForFilteredProduct,
        AssociationInterface $XSELLForFullProduct,
        AssociationTypeInterface $associationTypeXSELLForFullProduct
    ) {
        $productB->getIdentifier()->willReturn('product_b');
        $productC->getIdentifier()->willReturn('product_c');
        $productD->getIdentifier()->willReturn('product_d');

        $fullProduct->getAssociations()->willReturn([$XSELLForFullProduct]);
        $XSELLForFullProduct->getAssociationType()->willReturn($associationTypeXSELLForFullProduct);
        $XSELLForFullProduct->getProducts()->willReturn([$productB]);
        $associationTypeXSELLForFullProduct->getCode()->willReturn('X_SELL');
        $authorizationChecker->isGranted([Attributes::VIEW], $productB)->willReturn(false);

        $filteredProduct->getAssociations()->willReturn([$UPSELLForFilteredProduct]);
        $UPSELLForFilteredProduct->getAssociationType()->willReturn($associationTypeUPSELLForFilteredProduct);
        $UPSELLForFilteredProduct->getProducts()->willReturn([$productC, $productD]);
        $UPSELLForFilteredProduct->getGroups()->willReturn([]);
        $associationTypeUPSELLForFilteredProduct->getCode()->willReturn('UPSELL');

        $associationSetter->setFieldData($fullProduct, 'associations', [
            'X_SELL' => ['products' => ['product_b']],
            'UPSELL' => ['products' => ['product_c', 'product_d']]
        ])->shouldBeCalled();

        $this->merge($filteredProduct, $fullProduct)->shouldReturn($fullProduct);
    }

    function it_merges_not_granted_associated_products_and_removes_granted_product(
        $authorizationChecker,
        $associationSetter,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        ProductInterface $productB,
        ProductInterface $productC,
        ProductInterface $productD,
        AssociationInterface $XSELLForFilteredProduct,
        AssociationTypeInterface $associationTypeXSELLForFilteredProduct,
        AssociationInterface $XSELLForFullProduct,
        AssociationTypeInterface $associationTypeXSELLForFullProduct
    ) {
        $productB->getIdentifier()->willReturn('product_b');
        $productC->getIdentifier()->willReturn('product_c');
        $productD->getIdentifier()->willReturn('product_d');

        $fullProduct->getAssociations()->willReturn([$XSELLForFullProduct]);
        $XSELLForFullProduct->getAssociationType()->willReturn($associationTypeXSELLForFullProduct);
        $XSELLForFullProduct->getProducts()->willReturn([$productB, $productC]);
        $associationTypeXSELLForFullProduct->getCode()->willReturn('X_SELL');
        $authorizationChecker->isGranted([Attributes::VIEW], $productB)->willReturn(false);
        $authorizationChecker->isGranted([Attributes::VIEW], $productC)->willReturn(true);

        $filteredProduct->getAssociations()->willReturn([$XSELLForFilteredProduct]);
        $XSELLForFilteredProduct->getAssociationType()->willReturn($associationTypeXSELLForFilteredProduct);
        $XSELLForFilteredProduct->getProducts()->willReturn([]);
        $XSELLForFilteredProduct->getGroups()->willReturn([]);
        $associationTypeXSELLForFilteredProduct->getCode()->willReturn('X_SELL');

        $associationSetter->setFieldData($fullProduct, 'associations', [
            'X_SELL' => ['products' => ['product_b']]
        ])->shouldBeCalled();

        $this->merge($filteredProduct, $fullProduct)->shouldReturn($fullProduct);
    }

    function it_throws_an_exception_if_filtered_subject_is_not_a_product()
    {
        $this->shouldThrow(InvalidObjectException::objectExpected(ClassUtils::getClass(new \stdClass()), ProductInterface::class))
            ->during('merge', [new \stdClass(), new Product()]);
    }

    function it_throws_an_exception_if_full_subject_is_not_a_product()
    {
        $this->shouldThrow(InvalidObjectException::objectExpected(ClassUtils::getClass(new \stdClass()), ProductInterface::class))
            ->during('merge', [new Product(), new \stdClass()]);
    }
}
