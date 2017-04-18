<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Pim\Bundle\CatalogBundle\EventSubscriber\LoadProductValuesSubscriber;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\ProductValueCollectionFactory;
use Pim\Component\Catalog\Factory\ProductValueFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueCollectionInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LoadProductValuesSubscriberSpec extends ObjectBehavior
{
    function let(
        ContainerInterface $container,
        NormalizerInterface $serializer,
        ProductValueFactory $valueFactory,
        ProductValueCollectionFactory $valueCollectionFactory,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($container);

        $container->get('pim_serializer')->willReturn($serializer);
        $container->get('pim_catalog.factory.product_value')->willReturn($valueFactory);
        $container->get('pim_catalog.factory.product_value_collection')->willReturn($valueCollectionFactory);
        $container->get('pim_catalog.repository.attribute')->willReturn($attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LoadProductValuesSubscriber::class);
    }

    function it_subscribes_to_the_postLoad_event()
    {
        $this->getSubscribedEvents()->shouldReturn(['postLoad']);
    }

    function it_loads_values_of_a_product(
        $serializer,
        $valueFactory,
        $valueCollectionFactory,
        $attributeRepository,
        LifecycleEventArgs $event,
        ProductInterface $product,
        AttributeInterface $sku,
        ProductValueCollectionInterface $values,
        ProductValueInterface $skuValue
    ) {
        $event->getObject()->willReturn($product);
        $attributeRepository->getIdentifier()->willReturn($sku);
        $product->getIdentifier()->willReturn('foo');

        $valueFactory->create($sku, null, null, 'foo')->willReturn($skuValue);
        $serializer->normalize($skuValue, 'storage')->willReturn(['sku' => 'raw_identifier_value']);

        $skuValue->getAttribute()->willReturn($sku);
        $sku->getCode()->willReturn('sku');
        $product->getRawValues()->willReturn(['other_values' => 'raw_values']);

        $valueCollectionFactory
            ->createFromStorageFormat(['other_values' => 'raw_values', 'sku' => 'raw_identifier_value'])
            ->willReturn($values);

        $product->setValues($values)->shouldBeCalled();

        $this->postLoad($event);
    }

    function it_works_only_with_products($container, LifecycleEventArgs $event, \stdClass $object)
    {
        $event->getObject()->willReturn($object);
        $container->get(Argument::any())->shouldNotBeCalled();

        $this->postLoad($event);
    }
}
