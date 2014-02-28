<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Pim\Bundle\CatalogBundle\Model\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\AvailableAttributes;

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
        ProductRepositoryInterface $repository
    ) {
        $entityConfig = array(
            'flexible_class' => self::PRODUCT_CLASS,
            'flexible_value_class' => self::VALUE_CLASS,
            'attribute_class' => self::ATTRIBUTE_CLASS,
            'attribute_option_class' => self::OPTION_CLASS,
            'attribute_option_value_class' => self::OPT_VALUE_CLASS
        );

        $objectManager->getRepository(self::PRODUCT_CLASS)->willReturn($repository);

        $this->beConstructedWith(
            $entityConfig,
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

    function it_has_a_product_repository(ProductRepositoryInterface $repository)
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

    function it_creates_a_product($entityManager, AttributeRepository $attRepository, Attribute $sku)
    {
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

    function it_adds_attributes_to_product(
        $entityManager,
        $builder,
        AttributeRepository $attRepository,
        ProductInterface $product,
        AvailableAttributes $attributes,
        Attribute $sku,
        Attribute $name,
        Attribute $size
    ) {
        $attributes->getAttributes()->willReturn([$sku, $name, $size]);

        $builder->addAttributeToProduct($product, $sku)->shouldBeCalled();
        $builder->addAttributeToProduct($product, $name)->shouldBeCalled();
        $builder->addAttributeToProduct($product, $size)->shouldBeCalled();

        $this->addAttributesToProduct($product, $attributes);
    }

    function it_checks_value_existence(
        ProductRepositoryInterface $repository,
        ProductValueInterface $value
    ) {
        $repository->setFlexibleConfig($this->getFlexibleConfig())->shouldBeCalled();
        $repository->valueExists($value)->willReturn(true);
        $this->valueExists($value)->shouldReturn(true);

        $repository->valueExists($value)->willReturn(false);
        $this->valueExists($value)->shouldReturn(false);
    }
}
