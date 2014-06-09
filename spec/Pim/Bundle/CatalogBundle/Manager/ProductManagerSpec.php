<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\AssociationTypeRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeOptionRepository;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\AvailableAttributes;
use Pim\Bundle\CatalogBundle\Persistence\ProductPersister;

class ProductManagerSpec extends ObjectBehavior
{
    const PRODUCT_CLASS   = 'Pim\Bundle\CatalogBundle\Model\Product';
    const VALUE_CLASS     = 'Pim\Bundle\CatalogBundle\Model\ProductValue';
    const ATTRIBUTE_CLASS = 'Pim\Bundle\CatalogBundle\Entity\Attribute';
    const OPTION_CLASS    = 'Pim\Bundle\CatalogBundle\Entity\AttributeOption';
    const OPT_VALUE_CLASS = 'Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue';

    function let(
        ObjectManager $objectManager,
        ProductPersister $persister,
        EventDispatcherInterface $eventDispatcher,
        MediaManager $mediaManager,
        ProductBuilder $builder,
        ProductRepositoryInterface $productRepository,
        AssociationTypeRepository $associationTypeRepository,
        AttributeRepository $attributeRepository,
        AttributeOptionRepository $attributeOptionRepository
    ) {
        $entityConfig = array(
            'product_class' => self::PRODUCT_CLASS,
            'product_value_class' => self::VALUE_CLASS,
            'attribute_class' => self::ATTRIBUTE_CLASS,
            'attribute_option_class' => self::OPTION_CLASS,
            'attribute_option_value_class' => self::OPT_VALUE_CLASS
        );

        $this->beConstructedWith(
            $entityConfig,
            $objectManager,
            $persister,
            $eventDispatcher,
            $mediaManager,
            $builder,
            $productRepository,
            $associationTypeRepository,
            $attributeRepository,
            $attributeOptionRepository
        );
    }

    function it_has_a_product_repository(ProductRepositoryInterface $productRepository)
    {
        $this->getProductRepository()->shouldReturn($productRepository);
    }

    function it_has_an_attribute_option_repository(AttributeOptionRepository $attributeOptionRepository)
    {
        $this->getAttributeOptionRepository()->shouldReturn($attributeOptionRepository);
    }

    function it_creates_a_product()
    {
        $this->createProduct()->shouldReturnAnInstanceOf(self::PRODUCT_CLASS);
    }

    function it_creates_a_product_value()
    {
        $this->createProductValue()->shouldReturnAnInstanceOf(self::VALUE_CLASS);
    }

    function it_provides_the_identifier_attribute(AttributeRepository $attributeRepository, AbstractAttribute $sku)
    {
        $attributeRepository->findOneBy(['attributeType' => 'pim_catalog_identifier'])->willReturn($sku);

        $this->getIdentifierAttribute()->shouldReturn($sku);
    }

    function it_adds_attributes_to_product(
        $entityManager,
        $builder,
        AttributeRepository $attRepository,
        ProductInterface $product,
        AvailableAttributes $attributes,
        AbstractAttribute $sku,
        AbstractAttribute $name,
        AbstractAttribute $size
    ) {
        $attributes->getAttributes()->willReturn([$sku, $name, $size]);

        $builder->addAttributeToProduct($product, $sku)->shouldBeCalled();
        $builder->addAttributeToProduct($product, $name)->shouldBeCalled();
        $builder->addAttributeToProduct($product, $size)->shouldBeCalled();

        $this->addAttributesToProduct($product, $attributes);
    }

    function it_checks_value_existence(ProductRepositoryInterface $productRepository, ProductValueInterface $value)
    {
        $productRepository->valueExists($value)->willReturn(true);
        $this->valueExists($value)->shouldReturn(true);

        $productRepository->valueExists($value)->willReturn(false);
        $this->valueExists($value)->shouldReturn(false);
    }

    function it_delegates_database_product_synchronization_to_the_product_persister(
        ProductPersister $persister,
        ProductInterface $product
    ) {
        $persister->persist($product, ['recalculate' => true, 'flush' => true, 'schedule' => true])->shouldBeCalled();

        $this->save($product);
    }
}
