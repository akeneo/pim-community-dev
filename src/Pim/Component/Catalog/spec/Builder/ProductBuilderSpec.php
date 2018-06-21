<?php

namespace spec\Pim\Component\Catalog\Builder;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Association\MissingAssociationAdder;
use Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface;
use Pim\Component\Catalog\Model\ProductAssociation;
use Pim\Component\Catalog\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Pim\Component\Catalog\Model\Product;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\ProductEvents;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductBuilderSpec extends ObjectBehavior
{
    const PRODUCT_CLASS = Product::class;
    const ASSOCIATION_CLASS = ProductAssociation::class;

    function let(
        AttributeRepositoryInterface $attributeRepository,
        FamilyRepositoryInterface $familyRepository,
        EventDispatcherInterface $eventDispatcher,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        MissingAssociationAdder $missingAssociationAdder
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
            $missingAssociationAdder,
            $entityConfig
        );
    }

    function it_creates_product_without_family($eventDispatcher)
    {
        $eventDispatcher->dispatch(ProductEvents::CREATE, Argument::any())->shouldBeCalled();

        $this->createProduct()->shouldReturnAnInstanceOf(self::PRODUCT_CLASS);
    }

    function it_creates_product_with_a_family_and_an_identifier(
        $familyRepository,
        $attributeRepository,
        $eventDispatcher,
        $entityWithValuesBuilder,
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
        );

        $familyRepository->findOneByIdentifier("tshirt")->willReturn($tshirtFamily);
        $tshirtFamily->getId()->shouldBeCalled();
        $tshirtFamily->getAttributes()->willReturn([]);

        $eventDispatcher->dispatch(ProductEvents::CREATE, Argument::any())->shouldBeCalled();

        $product = $this->createProduct('mysku', 'tshirt');
        $product->shouldReturnAnInstanceOf(self::PRODUCT_CLASS);
    }

    function it_adds_missing_product_associations(
        MissingAssociationAdder $missingAssociationAdder,
        EntityWithAssociationsInterface $productOne,
        EntityWithAssociationsInterface $productTwo,
        AssociationTypeInterface $type
    ) {
        $missingAssociationAdder->addMissingAssociations($productOne)->shouldBeCalled();
        $this->addMissingAssociations($productOne);
    }
}
