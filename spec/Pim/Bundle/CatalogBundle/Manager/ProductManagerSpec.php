<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Entity\Repository\AssociationTypeRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeOptionRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Event\ProductEvents;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\AvailableAttributes;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Saver\ProductSaver;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Prophecy\Argument;
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
            $productSaver,
            $productBulkSaver,
            $eventDispatcher,
            $mediaManager,
            $builder,
            $productRepository,
            $associationTypeRepository,
            $attributeRepository,
            $attributeOptionRepository
        );
    }

    function it_is_a_saver()
    {
        $this->shouldImplement('Pim\Component\Resource\Model\SaverInterface');
    }

    function it_is_a_bulk_saver()
    {
        $this->shouldImplement('Pim\Component\Resource\Model\BulkSaverInterface');
    }

    function it_is_a_remover()
    {
        $this->shouldImplement('Pim\Component\Resource\Model\RemoverInterface');
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

    function it_adds_attributes_to_product_and_save_it(
        $productSaver,
        $builder,
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

        $productSaver->save($product, ['recalculate' => false, 'schedule' => false])
            ->shouldBeCalled();

        $this->addAttributesToProduct($product, $attributes);
    }

    function it_adds_attributes_to_product_and_save_it_with_saving_options(
        $productSaver,
        $builder,
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

        $productSaver->save($product, ['recalculate' => false, 'flush' => false, 'schedule' => false])
            ->shouldBeCalled();

        $this->addAttributesToProduct($product, $attributes, ['flush' => false]);
    }

    function it_removes_attribute_from_product_and_save_it(
        $productSaver,
        ProductInterface $product,
        AvailableAttributes $attributes,
        AbstractAttribute $skuAttribute,
        AbstractAttribute $nameAttribute,
        ProductValueInterface $sku,
        ProductValueInterface $nameFr,
        ProductValueInterface $nameEn
    ) {
        $product->getValues()->willReturn([$sku, $nameFr, $nameEn]);
        $sku->getAttribute()->willReturn($skuAttribute);
        $nameFr->getAttribute()->willReturn($nameAttribute);
        $nameEn->getAttribute()->willReturn($nameAttribute);

        $product->removeValue($sku)->shouldNotBeCalled();
        $product->removeValue($nameFr)->shouldBeCalled();
        $product->removeValue($nameEn)->shouldBeCalled();

        $productSaver->save($product, ['recalculate' => false, 'schedule' => false])->shouldBeCalled();

        $this->removeAttributeFromProduct($product, $nameAttribute);
    }

    function it_removes_attribute_from_product_and_save_it_with_saving_options(
        $productSaver,
        ProductInterface $product,
        AvailableAttributes $attributes,
        AbstractAttribute $skuAttribute,
        AbstractAttribute $nameAttribute,
        ProductValueInterface $sku,
        ProductValueInterface $nameFr,
        ProductValueInterface $nameEn
    ) {
        $product->getValues()->willReturn([$sku, $nameFr, $nameEn]);
        $sku->getAttribute()->willReturn($skuAttribute);
        $nameFr->getAttribute()->willReturn($nameAttribute);
        $nameEn->getAttribute()->willReturn($nameAttribute);

        $product->removeValue($sku)->shouldNotBeCalled();
        $product->removeValue($nameFr)->shouldBeCalled();
        $product->removeValue($nameEn)->shouldBeCalled();

        $productSaver->save($product, ['recalculate' => false, 'flush' => false, 'schedule' => false])->shouldBeCalled();

        $this->removeAttributeFromProduct($product, $nameAttribute, ['flush' => false]);
    }

    function it_checks_value_existence(ProductRepositoryInterface $productRepository, ProductValueInterface $value)
    {
        $productRepository->valueExists($value)->willReturn(true);
        $this->valueExists($value)->shouldReturn(true);

        $productRepository->valueExists($value)->willReturn(false);
        $this->valueExists($value)->shouldReturn(false);
    }

    function it_throws_exception_when_remove_anything_else_than_a_product()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a Pim\Bundle\CatalogBundle\Model\ProductInterface, "%s" provided',
                        get_class($anythingElse)
                    )
                )
            )
            ->duringRemove($anythingElse);
    }

    function it_delegates_database_product_synchronization_to_the_product_saver(
        ProductSaver $productSaver,
        ProductInterface $product
    ) {
        $productSaver->save($product, [])->shouldBeCalled();

        $this->save($product);
    }

    function it_dispatches_an_event_when_removing_a_product(
        $eventDispatcher,
        $objectManager,
        ProductInterface $product
    ) {
        $eventDispatcher
            ->dispatch(
                ProductEvents::PRE_REMOVE,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();
        $eventDispatcher
            ->dispatch(
                ProductEvents::POST_REMOVE,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();

        $objectManager->remove($product)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->remove($product);
    }

    function it_does_not_flush_if_i_do_not_want_when_removing_a_product(
        $eventDispatcher,
        $objectManager,
        ProductInterface $product
    ) {
        $eventDispatcher
            ->dispatch(
                ProductEvents::PRE_REMOVE,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();
        $eventDispatcher
            ->dispatch(
                ProductEvents::POST_REMOVE,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();

        $objectManager->remove($product)->shouldBeCalled();
        $objectManager->flush()->shouldNotBeCalled();

        $this->remove($product, ['flush' => false]);
    }

    function it_saves_a_product($productSaver, ProductInterface $product)
    {
        $productSaver->save($product, ['option1'])->shouldBeCalled();

        $this->save($product, ['option1']);
    }

    function it_saves_products($productBulkSaver, ProductInterface $product1, ProductInterface $product2)
    {
        $productBulkSaver->saveAll([$product1, $product2], ['option1'])->shouldBeCalled();

        $this->saveAll([$product1, $product2], ['option1']);
    }
}
