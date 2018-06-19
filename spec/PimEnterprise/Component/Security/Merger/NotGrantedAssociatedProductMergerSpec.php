<?php

namespace spec\PimEnterprise\Component\Security\Merger;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Updater\Setter\FieldSetterInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Query\ItemCategoryAccessQuery;
use PimEnterprise\Component\Security\Merger\NotGrantedAssociatedProductMerger;
use PimEnterprise\Component\Security\NotGrantedDataMergerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class NotGrantedAssociatedProductMergerSpec extends ObjectBehavior
{
    function let(
        AuthorizationCheckerInterface $authorizationChecker,
        FieldSetterInterface $associationSetter,
        ItemCategoryAccessQuery $productCategoryAccessQuery,
        ItemCategoryAccessQuery $productModelCategoryAccessQuery,
        TokenStorageInterface $tokenStorage
    ) {
        $this->beConstructedWith(
            $authorizationChecker,
            $associationSetter,
            $productCategoryAccessQuery,
            $productModelCategoryAccessQuery,
            $tokenStorage
        );
    }

    function it_implements_a_not_granted_data_merger_interface()
    {
        $this->shouldImplement(NotGrantedDataMergerInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NotGrantedAssociatedProductMerger::class);
    }

    function it_merges_not_granted_associated_products_in_product(
        $associationSetter,
        $productCategoryAccessQuery,
        $productModelCategoryAccessQuery,
        $tokenStorage,
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
        TokenInterface $token,
        UserInterface $user,
        ArrayCollection $productCollection,
        ArrayCollection $productModelCollection,
        \ArrayIterator $productIterator,
        \ArrayIterator $productModelIterator
    ) {
        $productB->getId()->willReturn(1);
        $productB->getIdentifier()->willReturn('product_b');
        $productC->getId()->willReturn(2);
        $productC->getIdentifier()->willReturn('product_c');
        $productD->getId()->willReturn(3);
        $productD->getIdentifier()->willReturn('product_d');
        $productModelA->getId()->willReturn(3);
        $productModelA->getCode()->willReturn('product_model_a');
        $productModelB->getId()->willReturn(4);
        $productModelB->getCode()->willReturn('product_model_b');

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $fullProduct->getAssociations()->willReturn([$XSELLForFullProduct]);
        $XSELLForFullProduct->getAssociationType()->willReturn($associationTypeXSELLForFullProduct);
        $associationTypeXSELLForFullProduct->getCode()->willReturn('X_SELL');

        $productCollection->add($productB);
        $productCollection->add($productC);
        $productCollection->toArray()->willReturn([$productB, $productC]);
        $productCollection->getIterator()->willReturn($productIterator);
        $productIterator->rewind()->shouldBeCalled();
        $productIterator->valid()->willReturn(true, true, false);
        $productIterator->current()->willReturn($productB, $productC);
        $productIterator->next()->shouldBeCalled();

        $productModelCollection->add($productModelA);
        $productModelCollection->add($productModelB);
        $productModelCollection->toArray()->willReturn([$productModelA, $productModelB]);
        $productModelCollection->getIterator()->willReturn($productModelIterator);
        $productModelIterator->rewind()->shouldBeCalled();
        $productModelIterator->valid()->willReturn(true, true, false);
        $productModelIterator->current()->willReturn($productModelA, $productModelB);
        $productModelIterator->next()->shouldBeCalled();

        $XSELLForFullProduct->getProducts()->willReturn($productCollection);
        $XSELLForFullProduct->getProductModels()->willReturn($productModelCollection);

        $productCategoryAccessQuery->getGrantedItemIds([$productB, $productC], $user)->willReturn([2 => 2]);
        $productModelCategoryAccessQuery->getGrantedItemIds([$productModelA, $productModelB], $user)->willReturn([4 => 4]);

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
        $associationSetter,
        $tokenStorage,
        $productCategoryAccessQuery,
        $productModelCategoryAccessQuery,
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
        AssociationTypeInterface $associationTypeXSELLForFullProduct,
        TokenInterface $token,
        UserInterface $user,
        ArrayCollection $productCollection,
        ArrayCollection $productModelCollection,
        \ArrayIterator $productIterator,
        \ArrayIterator $productModelIterator
    ) {
        $productB->getId()->willReturn(1);
        $productB->getIdentifier()->willReturn('product_b');
        $productC->getId()->willReturn(2);
        $productC->getIdentifier()->willReturn('product_c');
        $productD->getId()->willReturn(3);
        $productD->getIdentifier()->willReturn('product_d');
        $productModelA->getId()->willReturn(1);
        $productModelA->getCode()->willReturn('product_model_a');
        $productModelB->getId()->willReturn(2);
        $productModelB->getCode()->willReturn('product_model_b');

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $fullProduct->getAssociations()->willReturn([$XSELLForFullProduct]);
        $XSELLForFullProduct->getAssociationType()->willReturn($associationTypeXSELLForFullProduct);
        $XSELLForFullProduct->getProducts()->willReturn($productCollection);
        $XSELLForFullProduct->getProductModels()->willReturn($productModelCollection);
        $associationTypeXSELLForFullProduct->getCode()->willReturn('X_SELL');

        $productCollection->add($productB);
        $productCollection->add($productC);
        $productCollection->add($productD);
        $productCollection->toArray()->willReturn([$productB, $productC, $productD]);
        $productCollection->getIterator()->willReturn($productIterator);
        $productIterator->rewind()->shouldBeCalled();
        $productIterator->valid()->willReturn(true, true, true, false);
        $productIterator->current()->willReturn($productB, $productC, $productD);
        $productIterator->next()->shouldBeCalled();

        $productModelCollection->add($productModelA);
        $productModelCollection->add($productModelB);
        $productModelCollection->toArray()->willReturn([$productModelA, $productModelB]);
        $productModelCollection->getIterator()->willReturn($productModelIterator);
        $productModelIterator->rewind()->shouldBeCalled();
        $productModelIterator->valid()->willReturn(true, true, false);
        $productModelIterator->current()->willReturn($productModelA, $productModelB);
        $productModelIterator->next()->shouldBeCalled();

        $productCategoryAccessQuery->getGrantedItemIds([$productB, $productC, $productD], $user)->willReturn([2 => 2, 3 => 3]);
        $productModelCategoryAccessQuery->getGrantedItemIds([$productModelA, $productModelB], $user)->willReturn([2 => 2]);

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
        $tokenStorage,
        $productCategoryAccessQuery,
        $productModelCategoryAccessQuery,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        ProductInterface $productB,
        ProductInterface $productC,
        ProductModelInterface $productModelA,
        ProductModelInterface $productModelB,
        AssociationInterface $XSELLForFilteredProduct,
        AssociationTypeInterface $associationTypeXSELLForFilteredProduct,
        AssociationInterface $XSELLForFullProduct,
        AssociationTypeInterface $associationTypeXSELLForFullProduct,
        TokenInterface $token,
        UserInterface $user,
        ArrayCollection $productCollection,
        ArrayCollection $productModelCollection,
        \ArrayIterator $productIterator,
        \ArrayIterator $productModelIterator
    ) {
        $productB->getId()->willReturn(1);
        $productB->getIdentifier()->willReturn('product_b');
        $productC->getId()->willReturn(2);
        $productC->getIdentifier()->willReturn('product_c');
        $productModelA->getId()->willReturn(1);
        $productModelA->getCode()->willReturn('product_model_a');
        $productModelB->getId()->willReturn(2);
        $productModelB->getCode()->willReturn('product_model_b');

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $fullProduct->getAssociations()->willReturn([$XSELLForFullProduct]);
        $XSELLForFullProduct->getAssociationType()->willReturn($associationTypeXSELLForFullProduct);
        $XSELLForFullProduct->getProducts()->willReturn($productCollection);
        $XSELLForFullProduct->getProductModels()->willReturn($productModelCollection);
        $associationTypeXSELLForFullProduct->getCode()->willReturn('X_SELL');

        $productCollection->add($productB);
        $productCollection->add($productC);
        $productCollection->toArray()->willReturn([$productB, $productC]);
        $productCollection->getIterator()->willReturn($productIterator);
        $productIterator->rewind()->shouldBeCalled();
        $productIterator->valid()->willReturn(true, true, false);
        $productIterator->current()->willReturn($productB, $productC);
        $productIterator->next()->shouldBeCalled();

        $productModelCollection->add($productModelA);
        $productModelCollection->add($productModelB);
        $productModelCollection->toArray()->willReturn([$productModelA, $productModelB]);
        $productModelCollection->getIterator()->willReturn($productModelIterator);
        $productModelIterator->rewind()->shouldBeCalled();
        $productModelIterator->valid()->willReturn(true, true, false);
        $productModelIterator->current()->willReturn($productModelA, $productModelB);
        $productModelIterator->next()->shouldBeCalled();

        $productCategoryAccessQuery->getGrantedItemIds([$productB, $productC], $user)->willReturn([2 => 2]);
        $productModelCategoryAccessQuery->getGrantedItemIds([$productModelA, $productModelB], $user)->willReturn([1 => 1]);

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
