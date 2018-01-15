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
use Pim\Component\Catalog\Model\ProductModelInterface;
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
        ProductModelInterface $productModelA,
        ProductModelInterface $productModelB,
        AssociationInterface $XSELLForFilteredProduct,
        AssociationTypeInterface $associationTypeXSELLForFilteredProduct,
        AssociationInterface $XSELLForFullProduct,
        AssociationTypeInterface $associationTypeXSELLForFullProduct
    ) {
        $productB->getIdentifier()->willReturn('product_b');
        $productC->getIdentifier()->willReturn('product_c');
        $productD->getIdentifier()->willReturn('product_d');
        $productModelA->getCode()->willReturn('product_model_a');
        $productModelB->getCode()->willReturn('product_model_b');

        $fullProduct->getAssociations()->willReturn([$XSELLForFullProduct]);
        $XSELLForFullProduct->getAssociationType()->willReturn($associationTypeXSELLForFullProduct);
        $XSELLForFullProduct->getProducts()->willReturn([$productB, $productC]);
        $XSELLForFullProduct->getProductModels()->willReturn(new ArrayCollection([$productModelA->getWrappedObject(), $productModelB->getWrappedObject()]));

        $associationTypeXSELLForFullProduct->getCode()->willReturn('X_SELL');
        $authorizationChecker->isGranted([Attributes::VIEW], $productB)->willReturn(false);
        $authorizationChecker->isGranted([Attributes::VIEW], $productC)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::VIEW], $productModelA)->willReturn(false);
        $authorizationChecker->isGranted([Attributes::VIEW], $productModelB)->willReturn(true);

        $filteredProduct->getAssociations()->willReturn([$XSELLForFilteredProduct]);
        $XSELLForFilteredProduct->getAssociationType()->willReturn($associationTypeXSELLForFilteredProduct);
        $XSELLForFilteredProduct->getProducts()->willReturn([$productC, $productD]);
        $XSELLForFilteredProduct->getProductModels()->willReturn(new ArrayCollection([$productModelB->getWrappedObject()]));
        $XSELLForFilteredProduct->getGroups()->willReturn([]);
        $associationTypeXSELLForFilteredProduct->getCode()->willReturn('X_SELL');

        $associationSetter->setFieldData($fullProduct, 'associations', [
            'X_SELL' => ['products' => ['product_b', 'product_c', 'product_d'], 'product_models' => ['product_model_a', 'product_model_b']]
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
        ProductModelInterface $productModelA,
        ProductModelInterface $productModelB,
        AssociationInterface $UPSELLForFilteredProduct,
        AssociationTypeInterface $associationTypeUPSELLForFilteredProduct,
        AssociationInterface $XSELLForFullProduct,
        AssociationTypeInterface $associationTypeXSELLForFullProduct
    ) {
        $productB->getIdentifier()->willReturn('product_b');
        $productC->getIdentifier()->willReturn('product_c');
        $productD->getIdentifier()->willReturn('product_d');
        $productModelA->getCode()->willReturn('product_model_a');
        $productModelB->getCode()->willReturn('product_model_b');

        $fullProduct->getAssociations()->willReturn([$XSELLForFullProduct]);
        $XSELLForFullProduct->getAssociationType()->willReturn($associationTypeXSELLForFullProduct);
        $XSELLForFullProduct->getProducts()->willReturn([$productB]);
        $XSELLForFullProduct->getProductModels()->willReturn(new ArrayCollection([$productModelA->getWrappedObject(), $productModelB->getWrappedObject()]));
        $associationTypeXSELLForFullProduct->getCode()->willReturn('X_SELL');
        $authorizationChecker->isGranted([Attributes::VIEW], $productB)->willReturn(false);
        $authorizationChecker->isGranted([Attributes::VIEW], $productC)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::VIEW], $productD)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::VIEW], $productModelA)->willReturn(false);
        $authorizationChecker->isGranted([Attributes::VIEW], $productModelB)->willReturn(true);

        $filteredProduct->getAssociations()->willReturn([$UPSELLForFilteredProduct]);
        $UPSELLForFilteredProduct->getAssociationType()->willReturn($associationTypeUPSELLForFilteredProduct);
        $UPSELLForFilteredProduct->getProducts()->willReturn([$productC, $productD]);
        $UPSELLForFilteredProduct->getProductModels()->willReturn(new ArrayCollection([$productModelA->getWrappedObject()]));
        $UPSELLForFilteredProduct->getGroups()->willReturn([]);
        $associationTypeUPSELLForFilteredProduct->getCode()->willReturn('UPSELL');

        $associationSetter->setFieldData($fullProduct, 'associations', [
            'X_SELL' => ['products' => ['product_b'], 'product_models' => ['product_model_a']],
            'UPSELL' => ['products' => ['product_c', 'product_d'], "product_models" => ["product_model_a"]]
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
        ProductModelInterface $productModelA,
        ProductModelInterface $productModelB,
        AssociationInterface $XSELLForFilteredProduct,
        AssociationTypeInterface $associationTypeXSELLForFilteredProduct,
        AssociationInterface $XSELLForFullProduct,
        AssociationTypeInterface $associationTypeXSELLForFullProduct,
        Collection $productModels,
        \Iterator $iteratorProductModels
    ) {
        $productB->getIdentifier()->willReturn('product_b');
        $productC->getIdentifier()->willReturn('product_c');
        $productD->getIdentifier()->willReturn('product_d');
        $productModelA->getCode()->willReturn('product_model_a');
        $productModelB->getCode()->willReturn('product_model_b');

        $fullProduct->getAssociations()->willReturn([$XSELLForFullProduct]);
        $XSELLForFullProduct->getAssociationType()->willReturn($associationTypeXSELLForFullProduct);
        $XSELLForFullProduct->getProducts()->willReturn([$productB, $productC]);
        $XSELLForFullProduct->getProductModels()->willReturn(new ArrayCollection([$productModelA->getWrappedObject(), $productModelB->getWrappedObject()]));
        $associationTypeXSELLForFullProduct->getCode()->willReturn('X_SELL');
        $authorizationChecker->isGranted([Attributes::VIEW], $productB)->willReturn(false);
        $authorizationChecker->isGranted([Attributes::VIEW], $productC)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::VIEW], $productModelA)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::VIEW], $productModelB)->willReturn(false);

        $filteredProduct->getAssociations()->willReturn([$XSELLForFilteredProduct]);
        $XSELLForFilteredProduct->getAssociationType()->willReturn($associationTypeXSELLForFilteredProduct);
        $XSELLForFilteredProduct->getProducts()->willReturn([]);
        $XSELLForFilteredProduct->getProductModels()->willReturn(new ArrayCollection([$productModelA->getWrappedObject()]));
        $XSELLForFilteredProduct->getGroups()->willReturn([]);
        $associationTypeXSELLForFilteredProduct->getCode()->willReturn('X_SELL');

        $associationSetter->setFieldData($fullProduct, 'associations', [
            'X_SELL' => ['products' => ['product_b'], 'product_models' => ['product_model_b', 'product_model_a']]
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
