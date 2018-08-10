<?php

namespace spec\Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductUniqueDataSynchronizer;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ProductUniqueDataFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductUniqueDataInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
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

    function it_synchronizes_unique_values(
        $factory,
        $repository,
        ProductInterface $product,
        Collection $uniqueDataCollectionToUpdate,
        ValueCollectionInterface $values,
        ValueInterface $skuValue,
        ValueInterface $nameValue,
        ProductUniqueDataInterface $eanUniqueData,
        ProductUniqueDataInterface $skuUniqueData,
        ProductUniqueDataInterface $newNameUniqueData,
        ProductUniqueDataInterface $newSkuUniqueData,
        AttributeInterface $skuAttribute,
        AttributeInterface $eanAttribute,
        AttributeInterface $nameAttribute
    ) {
        $product->getUniqueData()->willReturn($uniqueDataCollectionToUpdate);
        $uniqueDataCollectionToUpdate->toArray()->willReturn([$skuUniqueData, $eanUniqueData]);

        $product->getValues()->willReturn($values);
        $product->getValue('sku')->willReturn($skuValue);
        $skuUniqueData->setProductValue($skuValue)->shouldBeCalled();
        $values->getUniqueValues()->willReturn([$skuValue, $nameValue]);

        $uniqueDataCollectionToUpdate->removeElement($eanUniqueData)->shouldBeCalled();
        $uniqueDataCollectionToUpdate->add($newNameUniqueData)->shouldBeCalled();

        $skuValue->getAttribute()->willReturn($skuAttribute);
        $nameValue->getAttribute()->willReturn($nameAttribute);

        $skuAttribute->getCode()->willReturn('sku');
        $nameAttribute->getCode()->willReturn('name');
        $eanAttribute->getCode()->willReturn('ean');

        $eanUniqueData->getAttribute()->willReturn($eanAttribute);
        $skuUniqueData->getAttribute()->willReturn($skuAttribute);
        $newSkuUniqueData->getAttribute()->willReturn($skuAttribute);
        $newNameUniqueData->getAttribute()->willReturn($nameAttribute);


        $factory->create($product, $skuValue)->willReturn($newSkuUniqueData);
        $factory->create($product, $nameValue)->willReturn($newNameUniqueData);

        $this->synchronize($product);
    }
}
