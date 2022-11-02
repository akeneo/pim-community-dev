<?php

namespace Specification\Akeneo\Pim\Permission\Component\Merger;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\UserManagement\Component\Model\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FieldSetterInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Query\ItemCategoryAccessQuery;
use Akeneo\Pim\Permission\Component\Merger\NotGrantedAssociatedProductMerger;
use Akeneo\Pim\Permission\Component\NotGrantedDataMergerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class NotGrantedAssociatedProductMergerSpec extends ObjectBehavior
{
    function let(
        FieldSetterInterface $associationSetter,
        ItemCategoryAccessQuery $productCategoryAccessQuery,
        ItemCategoryAccessQuery $productModelCategoryAccessQuery,
        TokenStorageInterface $tokenStorage
    ) {
        $this->beConstructedWith(
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
        ArrayCollection $productCollection,
        ArrayCollection $productModelCollection,
        \ArrayIterator $productIterator,
        \ArrayIterator $productModelIterator
    ) {
        $user = new User();
        $productBUuid = 'b816e413-253e-4e63-ae1e-562deb93558f';
        $productB->getUuid()->willReturn(Uuid::fromString($productBUuid));
        $productB->getIdentifier()->willReturn('product_b');
        $productCUuid = '6fc5f000-2c00-4eac-899b-a85e41d6a42d';
        $productC->getUuid()->willReturn(Uuid::fromString($productCUuid));
        $productC->getIdentifier()->willReturn('product_c');
        $productDUuid = 'aab1fcbf-bacb-430c-8a90-b9d34db2d676';
        $productD->getUuid()->willReturn(Uuid::fromString($productDUuid));
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

        $productCategoryAccessQuery->getGrantedProductUuids([$productB, $productC], $user)->willReturn([
            '6fc5f000-2c00-4eac-899b-a85e41d6a42d',
            'aab1fcbf-bacb-430c-8a90-b9d34db2d676',
        ]);
        $productModelCategoryAccessQuery->getGrantedItemIds([$productModelA, $productModelB], $user)->willReturn([4 => 4]);

        $filteredProduct->getAssociations()->willReturn([$XSELLForFilteredProduct]);
        $XSELLForFilteredProduct->getAssociationType()->willReturn($associationTypeXSELLForFilteredProduct);
        $XSELLForFilteredProduct->getProducts()->willReturn([$productC, $productD]);
        $XSELLForFilteredProduct->getProductModels()->willReturn(new ArrayCollection([$productModelB->getWrappedObject()]));
        $XSELLForFilteredProduct->getGroups()->willReturn([]);
        $associationTypeXSELLForFilteredProduct->getCode()->willReturn('X_SELL');

        $associationSetter->setFieldData(
            $fullProduct,
            'associations',
            [
                'X_SELL' => [
                    'product_uuids' => [$productBUuid, $productCUuid, $productDUuid],
                    'product_models' => ['product_model_a', 'product_model_b'],
                    'groups' => [],
                ],
            ]
        )->shouldBeCalled();

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
        ArrayCollection $productCollection,
        ArrayCollection $productModelCollection,
        \ArrayIterator $productIterator,
        \ArrayIterator $productModelIterator
    ) {
        $user = new User();
        $productBUuid = 'b816e413-253e-4e63-ae1e-562deb93558f';
        $productB->getUuid()->willReturn(Uuid::fromString($productBUuid));
        $productB->getIdentifier()->willReturn('product_b');
        $productCUuid = '6fc5f000-2c00-4eac-899b-a85e41d6a42d';
        $productC->getUuid()->willReturn(Uuid::fromString($productCUuid));
        $productC->getIdentifier()->willReturn('product_c');
        $productDUuid = 'aab1fcbf-bacb-430c-8a90-b9d34db2d676';
        $productD->getUuid()->willReturn(Uuid::fromString($productDUuid));
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

        $productCategoryAccessQuery->getGrantedProductUuids([$productB, $productC, $productD], $user)->willReturn([
            '6fc5f000-2c00-4eac-899b-a85e41d6a42d',
            'aab1fcbf-bacb-430c-8a90-b9d34db2d676',
        ]);
        $productModelCategoryAccessQuery->getGrantedItemIds([$productModelA, $productModelB], $user)->willReturn([2 => 2]);

        $filteredProduct->getAssociations()->willReturn([$UPSELLForFilteredProduct]);
        $UPSELLForFilteredProduct->getAssociationType()->willReturn($associationTypeUPSELLForFilteredProduct);
        $UPSELLForFilteredProduct->getProducts()->willReturn([$productC, $productD]);
        $UPSELLForFilteredProduct->getProductModels()->willReturn(new ArrayCollection([$productModelA->getWrappedObject()]));
        $UPSELLForFilteredProduct->getGroups()->willReturn([]);
        $associationTypeUPSELLForFilteredProduct->getCode()->willReturn('UPSELL');

        $associationSetter->setFieldData(
            $fullProduct,
            'associations',
            [
                'X_SELL' => [
                    'product_uuids' => [$productBUuid],
                    'product_models' => ['product_model_a'],
                    'groups' => []
                ],
                'UPSELL' => [
                    'product_uuids' => [$productCUuid, $productDUuid],
                    'product_models' => ['product_model_a'],
                    'groups' => [],
                ],
            ]
        )->shouldBeCalled();

        $this->merge($filteredProduct, $fullProduct)->shouldReturn($fullProduct);
    }

    function it_merges_not_granted_associated_products_and_removes_granted_product(
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
        ArrayCollection $productCollection,
        ArrayCollection $productModelCollection,
        \ArrayIterator $productIterator,
        \ArrayIterator $productModelIterator
    ) {
        $user = new User();
        $productBUuid = 'b816e413-253e-4e63-ae1e-562deb93558f';
        $productB->getUuid()->willReturn(Uuid::fromString($productBUuid));
        $productB->getIdentifier()->willReturn('product_b');
        $productCUuid = '6fc5f000-2c00-4eac-899b-a85e41d6a42d';
        $productC->getUuid()->willReturn(Uuid::fromString($productCUuid));
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

        $productCategoryAccessQuery->getGrantedProductUuids([$productB, $productC], $user)->willReturn([
            '6fc5f000-2c00-4eac-899b-a85e41d6a42d',
        ]);
        $productModelCategoryAccessQuery->getGrantedItemIds([$productModelA, $productModelB], $user)->willReturn([1 => 1]);

        $filteredProduct->getAssociations()->willReturn([$XSELLForFilteredProduct]);
        $XSELLForFilteredProduct->getAssociationType()->willReturn($associationTypeXSELLForFilteredProduct);
        $XSELLForFilteredProduct->getProducts()->willReturn([]);
        $XSELLForFilteredProduct->getProductModels()->willReturn(new ArrayCollection([$productModelA->getWrappedObject()]));
        $XSELLForFilteredProduct->getGroups()->willReturn([]);
        $associationTypeXSELLForFilteredProduct->getCode()->willReturn('X_SELL');

        $associationSetter->setFieldData(
            $fullProduct,
            'associations',
            [
                'X_SELL' => [
                    'product_uuids' => [$productBUuid],
                    'product_models' => ['product_model_b', 'product_model_a'],
                    'groups' => [],
                ],
            ]
        )->shouldBeCalled();

        $this->merge($filteredProduct, $fullProduct)->shouldReturn($fullProduct);
    }

    function it_throws_an_exception_if_filtered_subject_is_not_a_product()
    {
        $this->shouldThrow(InvalidObjectException::objectExpected(ClassUtils::getClass(new \stdClass()), EntityWithAssociationsInterface::class))
            ->during('merge', [new \stdClass(), new Product()]);
    }

    function it_throws_an_exception_if_full_subject_is_not_a_product()
    {
        $this->shouldThrow(InvalidObjectException::objectExpected(ClassUtils::getClass(new \stdClass()), EntityWithAssociationsInterface::class))
            ->during('merge', [new Product(), new \stdClass()]);
    }
}
