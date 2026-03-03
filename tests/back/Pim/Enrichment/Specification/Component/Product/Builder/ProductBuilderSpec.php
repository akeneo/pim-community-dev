<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Builder;

use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\ProductEvents;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductBuilderSpec extends ObjectBehavior
{
    const PRODUCT_CLASS = Product::class;
    const ASSOCIATION_CLASS = ProductAssociation::class;

    function let(
        AttributeRepositoryInterface $attributeRepository,
        FamilyRepositoryInterface $familyRepository,
        EventDispatcherInterface $eventDispatcher,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder
    ) {
        $entityConfig = [
            'product' => self::PRODUCT_CLASS,
            'association' => self::ASSOCIATION_CLASS,
        ];

        $this->beConstructedWith(
            $attributeRepository,
            $familyRepository,
            $eventDispatcher,
            $entityWithValuesBuilder,
            $entityConfig
        );
    }

    function it_cannot_be_instantiated_without_product_class(
        AttributeRepositoryInterface $attributeRepository,
        FamilyRepositoryInterface $familyRepository,
        EventDispatcherInterface $eventDispatcher,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder
    ) {
        $this->beConstructedWith(
            $attributeRepository,
            $familyRepository,
            $eventDispatcher,
            $entityWithValuesBuilder,
            ['foo' => 'bar']
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_instantiated_with_a_non_product_interface_class(
        AttributeRepositoryInterface $attributeRepository,
        FamilyRepositoryInterface $familyRepository,
        EventDispatcherInterface $eventDispatcher,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder
    ) {
        $this->beConstructedWith(
            $attributeRepository,
            $familyRepository,
            $eventDispatcher,
            $entityWithValuesBuilder,
            ['product' => ProductModel::class]
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_creates_product_without_family(EventDispatcherInterface $eventDispatcher)
    {
        $eventDispatcher->dispatch(Argument::any(), ProductEvents::CREATE)->shouldBeCalled();

        $this->createProduct()->shouldReturnAnInstanceOf(self::PRODUCT_CLASS);
    }

    function it_creates_product_with_a_family_and_an_identifier(
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        EventDispatcherInterface $eventDispatcher,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        FamilyInterface $tshirtFamily,
        AttributeInterface $identifierAttribute
    ) {
        $attributeRepository->getIdentifier()->willReturn($identifierAttribute);
        $entityWithValuesBuilder->addOrReplaceValue(
            Argument::type(ProductInterface::class),
            $identifierAttribute,
            null,
            null,
            'mysku'
        )->shouldBeCalled();

        $familyRepository->findOneByIdentifier('tshirt')->willReturn($tshirtFamily);
        $eventDispatcher->dispatch(Argument::any(), ProductEvents::CREATE)->shouldBeCalled();

        $product = $this->createProduct('mysku', 'tshirt');
        $product->shouldReturnAnInstanceOf(self::PRODUCT_CLASS);
    }

    function it_creates_a_product_with_a_uuid(EventDispatcherInterface $eventDispatcher)
    {
        $uuid = '060309a1-8c7b-4cf3-9cd2-9acf545ff646';
        $eventDispatcher->dispatch(Argument::any(), ProductEvents::CREATE)->shouldBeCalled();

        $product = $this->createProduct(uuid: $uuid);
        $product->shouldBeAnInstanceOf(self::PRODUCT_CLASS);
        $product->getUuid()->shouldBeLike(Uuid::fromString($uuid));
    }

    function it_throws_an_exception_when_an_invalid_uuid_is_provided()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
             ->during('createProduct', ['uuid' => 'invalid-uuid']);
    }
}
