<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\ProductRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Doctrine\ORM\Mapping\ClassMetadata;

class ProductManagerSpec extends ObjectBehavior
{
    const PRODUCT_CLASS   = 'Pim\Bundle\CatalogBundle\Model\Product';
    const VALUE_CLASS     = 'Pim\Bundle\CatalogBundle\Model\ProductValue';
    const ATTRIBUTE_CLASS = 'Pim\Bundle\CatalogBundle\Entity\Attribute';
    const OPTION_CLASS    = 'Pim\Bundle\CatalogBundle\Entity\AttributeOption';
    const OPT_VALUE_CLASS = 'Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue';

    function let(
        ObjectManager $objectManager,
        EntityManager $entityManager,
        EventDispatcherInterface $eventDispatcher,
        MediaManager $mediaManager,
        CompletenessManager $completenessManager,
        ProductBuilder $builder,
        ProductRepository $repository,
        ClassMetadata $productMeta,
        ClassMetadata $valueMeta,
        ClassMetadata $attributeMeta,
        ClassMetadata $optionMeta,
        ClassMetadata $optionValueMeta
    ) {
        $objectManager->getClassMetadata(self::PRODUCT_CLASS)->willReturn($productMeta);
        $productMeta->getAssociationMappings()->willReturn(['values' => ['targetEntity' => self::VALUE_CLASS]]);
        $objectManager->getClassMetadata(self::VALUE_CLASS)->willReturn($valueMeta);
        $valueMeta->getAssociationMappings()->willReturn(['attribute' => ['targetEntity' => self::ATTRIBUTE_CLASS]]);
        $objectManager->getClassMetadata(self::ATTRIBUTE_CLASS)->willReturn($attributeMeta);
        $attributeMeta->getAssociationMappings()->willReturn(['options' => ['targetEntity' => self::OPTION_CLASS]]);
        $objectManager->getClassMetadata(self::OPTION_CLASS)->willReturn($optionMeta);
        $optionMeta->getAssociationMappings()->willReturn(['optionValues' => ['targetEntity' => self::OPT_VALUE_CLASS]]);

        $objectManager->getRepository(self::PRODUCT_CLASS)->willReturn($repository);

        $this->beConstructedWith(
            self::PRODUCT_CLASS,
            $objectManager,
            $entityManager,
            $eventDispatcher,
            $mediaManager,
            $completenessManager,
            $builder,
            $repository
        );
    }

    function it_is_a_flexible_manager()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager');
    }

    function it_has_a_product_repository(ProductRepository $repository)
    {
        $this->getFlexibleRepository()->shouldReturn($repository);
    }

    function it_has_a_locale()
    {
        $this->setLocale('fr_FR')->shouldReturn($this);
        $this->getLocale()->shouldReturn('fr_FR');
    }

    function it_has_a_scope()
    {
        $this->setScope('ecommerce')->shouldReturn($this);
        $this->getScope()->shouldReturn('ecommerce');
    }

    function it_creates_a_product(EntityManager $entityManager, AttributeRepository $attRepository, Attribute $sku)
    {
        $entityManager->getRepository(self::ATTRIBUTE_CLASS)->willReturn($attRepository);
        $attRepository->getCodeToAttributes(self::PRODUCT_CLASS)->willReturn(['sku' => $sku]);
        $this->createProduct()->shouldReturnAnInstanceOf(self::PRODUCT_CLASS);
    }

    function it_creates_a_product_value()
    {
        $this->createProductValue()->shouldReturnAnInstanceOf(self::VALUE_CLASS);
    }

    function it_gets_identifier_attribute(EntityManager $entityManager, AttributeRepository $attRepository, Attribute $sku)
    {
        $entityManager->getRepository(self::ATTRIBUTE_CLASS)->willReturn($attRepository);
        $attRepository->findOneBy(Argument::any())->willReturn($sku);
        $this->getIdentifierAttribute()->shouldReturnAnInstanceOf(self::ATTRIBUTE_CLASS);
    }
}
