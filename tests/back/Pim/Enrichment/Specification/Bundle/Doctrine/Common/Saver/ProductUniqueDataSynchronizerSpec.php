<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductUniqueDataSynchronizer;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ProductUniqueDataFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
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

    function let(ProductUniqueDataFactory $factory, IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($factory, $attributeRepository);
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
        AttributeInterface $nameAttribute,
        $attributeRepository
    ) {
        $product->getUniqueData()->willReturn($uniqueDataCollectionToUpdate);
        $uniqueDataCollectionToUpdate->toArray()->willReturn([$skuUniqueData, $eanUniqueData]);

        $product->getValues()->willReturn([$skuValue, $nameValue]);
        $product->getValue('sku')->willReturn($skuValue);

        $skuValue->__toString()->willReturn('sku-01');

        $skuUniqueData->setAttribute($skuAttribute)->shouldBeCalled();
        $skuUniqueData->setRawData('sku-01')->shouldBeCalled();

        $uniqueDataCollectionToUpdate->removeElement($eanUniqueData)->shouldBeCalled();
        $uniqueDataCollectionToUpdate->add($newNameUniqueData)->shouldBeCalled();

        $skuValue->getAttributeCode()->willReturn('sku');
        $attributeRepository->findOneByIdentifier('sku')->willReturn($skuAttribute);
        $nameValue->getAttributeCode()->willReturn('name');
        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $nameValue->__toString()->willReturn('my_name');

        $skuAttribute->getCode()->willReturn('sku');
        $skuAttribute->isUnique()->willReturn(true);
        $nameAttribute->getCode()->willReturn('name');
        $nameAttribute->isUnique()->willReturn(true);
        $eanAttribute->getCode()->willReturn('ean');
        $eanAttribute->isUnique()->willReturn(true);

        $eanUniqueData->getAttribute()->willReturn($eanAttribute);
        $skuUniqueData->getAttribute()->willReturn($skuAttribute);
        $newSkuUniqueData->getAttribute()->willReturn($skuAttribute);
        $newNameUniqueData->getAttribute()->willReturn($nameAttribute);

        $factory->create($product, $skuAttribute, 'sku-01')->willReturn($newSkuUniqueData);
        $factory->create($product, $nameAttribute, 'my_name')->willReturn($newNameUniqueData);

        $this->synchronize($product);
    }
}
