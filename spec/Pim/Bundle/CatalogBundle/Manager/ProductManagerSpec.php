<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductSaver;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\AssociationTypeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeOptionRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductManagerSpec extends ObjectBehavior
{
    const PRODUCT_CLASS   = 'Pim\Bundle\CatalogBundle\Model\Product';
    const VALUE_CLASS     = 'Pim\Bundle\CatalogBundle\Model\ProductValue';
    const ATTRIBUTE_CLASS = 'Pim\Bundle\CatalogBundle\Entity\Attribute';
    const OPTION_CLASS    = 'Pim\Bundle\CatalogBundle\Entity\AttributeOption';
    const OPT_VALUE_CLASS = 'Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue';

    function let(
        ObjectManager $objectManager,
        ProductSaver $productSaver,
        ProductSaver $productBulkSaver,
        EventDispatcherInterface $eventDispatcher,
        ProductBuilder $builder,
        ProductRepositoryInterface $productRepository,
        AssociationTypeRepositoryInterface $associationTypeRepository,
        AttributeRepositoryInterface $attributeRepository,
        AttributeOptionRepositoryInterface $attributeOptionRepository
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
            $productSaver,
            $productBulkSaver,
            $eventDispatcher,
            $builder,
            $productRepository,
            $associationTypeRepository,
            $attributeRepository,
            $attributeOptionRepository
        );
    }

    function it_is_a_product_manager()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Manager\ProductManagerInterface');
    }

    function it_has_a_product_repository(ProductRepositoryInterface $productRepository)
    {
        $this->getProductRepository()->shouldReturn($productRepository);
    }

    function it_has_an_attribute_option_repository(AttributeOptionRepositoryInterface $attributeOptionRepository)
    {
        $this->getAttributeOptionRepository()->shouldReturn($attributeOptionRepository);
    }

    function it_creates_a_product($builder)
    {
        $builder->createProduct()->shouldBeCalled();

        $this->createProduct();
    }

    function it_provides_the_identifier_attribute(AttributeRepositoryInterface $attributeRepository, AttributeInterface $sku)
    {
        $attributeRepository->getIdentifier()->shouldBeCalled();

        $this->getIdentifierAttribute();
    }

    function it_checks_value_existence(ProductRepositoryInterface $productRepository, ProductValueInterface $value)
    {
        $productRepository->valueExists($value)->willReturn(true);
        $this->valueExists($value)->shouldReturn(true);

        $productRepository->valueExists($value)->willReturn(false);
        $this->valueExists($value)->shouldReturn(false);
    }
}
