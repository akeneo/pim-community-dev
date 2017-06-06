<?php

namespace spec\Pim\Bundle\CatalogBundle\Saver\Common;

use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Factory\ProductUniqueDataFactory;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductUniqueDataSynchronizer;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductUniqueDataInterface;
use Pim\Component\Catalog\Model\ProductValueCollectionInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Prophecy\Argument;

class ProductUniqueDataSynchronizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductUniqueDataSynchronizer::class);
    }

    function let(ProductUniqueDataFactory $factory)
    {
        $this->beConstructedWith($factory);
    }

    function it_synchronizes_new_unique_values(
        $factory,
        ProductInterface $product,
        Collection $uniqueDataCollection,
        ProductValueCollectionInterface $values,
        ProductValueInterface $skuValue,
        AttributeInterface $sku,
        \ArrayIterator $uniqueDataCollectionIterator,
        ProductUniqueDataInterface $uniqueData
    ) {
        $product->getUniqueData()->willReturn($uniqueDataCollection);
        $product->getValues()->willReturn($values);
        $values->getUniqueValues()->willReturn([$skuValue]);

        $skuValue->getAttribute()->willReturn($sku);

        $uniqueDataCollection->getIterator()->willReturn($uniqueDataCollectionIterator);
        $uniqueDataCollectionIterator->rewind()->shouldBeCalled();
        $uniqueDataCollectionIterator->valid()->willReturn(false);

        $factory->create($product, $skuValue)->willReturn($uniqueData);
        $product->addUniqueData($uniqueData)->shouldBeCalled();

        $this->synchronize($product);
    }

    function it_synchronizes_existing_unique_values(
        $factory,
        ProductInterface $product,
        Collection $uniqueDataCollection,
        ProductValueCollectionInterface $values,
        ProductValueInterface $skuValue,
        AttributeInterface $sku,
        \ArrayIterator $uniqueDataCollectionIterator,
        ProductUniqueDataInterface $uniqueData
    ) {
        $product->getUniqueData()->willReturn($uniqueDataCollection);
        $product->getValues()->willReturn($values);
        $values->getUniqueValues()->willReturn([$skuValue]);

        $skuValue->getAttribute()->willReturn($sku);

        $uniqueDataCollection->getIterator()->willReturn($uniqueDataCollectionIterator);
        $uniqueDataCollectionIterator->rewind()->shouldBeCalled();
        $uniqueDataCollectionIterator->valid()->willReturn(true, false);
        $uniqueDataCollectionIterator->current()->willReturn($uniqueData);

        $uniqueData->getAttribute()->willReturn($sku);

        $factory->create(Argument::cetera())->shouldNotBeCalled($uniqueData);
        $product->addUniqueData($uniqueData)->shouldNotBeCalled();
        $uniqueData->setProductValue($skuValue)->shouldBeCalled();

        $this->synchronize($product);
    }
}
