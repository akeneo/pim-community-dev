<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Test\Common\Structure\Attribute\Builder;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductUniqueDataSynchronizer;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ProductUniqueDataFactory;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductUniqueDataInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

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
        ValueInterface $skuValue,
        ProductUniqueDataInterface $eanUniqueData,
        ProductUniqueDataInterface $skuUniqueData,
        ProductUniqueDataInterface $newNameUniqueData,
        ProductUniqueDataInterface $newSkuUniqueData,
        $attributeRepository
    ) {
        $product->getUniqueData()->willReturn($uniqueDataCollectionToUpdate);
        $uniqueDataCollectionToUpdate->toArray()->willReturn([$skuUniqueData, $eanUniqueData]);

        $product->getValues()->willReturn([ScalarValue::value('sku', 'sku-01'), ScalarValue::value('name', 'my_name')]);
        $product->getValue('sku')->willReturn(ScalarValue::value('sku', 'sku-01'));

        $skuAttribute = (new Builder())->withCode('sku')->aIdentifier()->build();
        $nameAttribute = (new Builder())->withCode('name')->aUniqueAttribute()->build();
        $eanAttribute = (new Builder())->withCode('ean')->aUniqueAttribute()->build();


        $skuUniqueData->setAttribute($skuAttribute)->shouldBeCalled();
        $skuUniqueData->setRawData('sku-01')->shouldBeCalled();

        $uniqueDataCollectionToUpdate->removeElement($eanUniqueData)->shouldBeCalled();
        $uniqueDataCollectionToUpdate->add($newNameUniqueData)->shouldBeCalled();

        $attributeRepository->findOneByIdentifier('sku')->willReturn($skuAttribute);
        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);

        $eanUniqueData->getAttribute()->willReturn($eanAttribute);
        $skuUniqueData->getAttribute()->willReturn($skuAttribute);
        $newSkuUniqueData->getAttribute()->willReturn($skuAttribute);
        $newNameUniqueData->getAttribute()->willReturn($nameAttribute);

        $factory->create($product, $skuAttribute, 'sku-01')->willReturn($newSkuUniqueData);
        $factory->create($product, $nameAttribute, 'my_name')->willReturn($newNameUniqueData);

        $this->synchronize($product);
    }

    function it_delete_unique_values_with_empty_data(
        $factory,
        $repository,
        ProductInterface $product,
        Collection $uniqueDataCollectionToUpdate,
        ValueInterface $skuValue,
        ProductUniqueDataInterface $eanUniqueData,
        ProductUniqueDataInterface $skuUniqueData,
        ProductUniqueDataInterface $newNameUniqueData,
        ProductUniqueDataInterface $newSkuUniqueData,
        $attributeRepository
    ) {
        $product->getUniqueData()->willReturn($uniqueDataCollectionToUpdate);
        $uniqueDataCollectionToUpdate->toArray()->willReturn([$skuUniqueData, $eanUniqueData]);

        $product->getValues()->willReturn([ScalarValue::value('sku', 'sku-01'), ScalarValue::value('name', 'my_name'), ScalarValue::value('ean', null)]);
        $product->getValue('sku')->willReturn(ScalarValue::value('sku', 'sku-01'));

        $skuAttribute = (new Builder())->withCode('sku')->aIdentifier()->build();
        $nameAttribute = (new Builder())->withCode('name')->aUniqueAttribute()->build();
        $eanAttribute = (new Builder())->withCode('ean')->aUniqueAttribute()->build();


        $skuUniqueData->setAttribute($skuAttribute)->shouldBeCalled();
        $skuUniqueData->setRawData('sku-01')->shouldBeCalled();

        $uniqueDataCollectionToUpdate->removeElement($eanUniqueData)->shouldBeCalled();
        $uniqueDataCollectionToUpdate->add($newNameUniqueData)->shouldBeCalled();

        $attributeRepository->findOneByIdentifier('sku')->willReturn($skuAttribute);
        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $attributeRepository->findOneByIdentifier('ean')->willReturn($eanAttribute);

        $eanUniqueData->getAttribute()->willReturn($eanAttribute);
        $skuUniqueData->getAttribute()->willReturn($skuAttribute);
        $newSkuUniqueData->getAttribute()->willReturn($skuAttribute);
        $newNameUniqueData->getAttribute()->willReturn($nameAttribute);

        $factory->create($product, $skuAttribute, 'sku-01')->willReturn($newSkuUniqueData);
        $factory->create($product, $nameAttribute, 'my_name')->willReturn($newNameUniqueData);

        $this->synchronize($product);
    }
}
