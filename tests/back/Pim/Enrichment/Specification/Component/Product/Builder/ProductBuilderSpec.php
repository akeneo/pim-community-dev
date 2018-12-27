<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Builder;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder;
use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductEvents;
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

        $familyRepository->findOneByIdentifier('tshirt')->willReturn($tshirtFamily);
        $tshirtFamily->getId()->shouldBeCalled();
        $tshirtFamily->getAttributes()->willReturn([]);

        $eventDispatcher->dispatch(ProductEvents::CREATE, Argument::any())->shouldBeCalled();

        $product = $this->createProduct('mysku', 'tshirt');
        $product->shouldReturnAnInstanceOf(self::PRODUCT_CLASS);
    }
}
