<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductUniqueDataSynchronizer;
use Pim\Component\Catalog\Factory\ProductUniqueDataFactory;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductUniqueDataInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Repository\ProductUniqueDataRepositoryInterface;
use Prophecy\Argument;

class ProductUniqueDataSynchronizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductUniqueDataSynchronizer::class);
    }

    function let(ProductUniqueDataFactory $factory, ProductUniqueDataRepositoryInterface $repository)
    {
        $this->beConstructedWith($factory, $repository);
    }

    function it_synchronizes_unique_values(
        $factory,
        $repository,
        ProductInterface $product,
        Collection $uniqueDataCollection,
        ValueCollectionInterface $values,
        ValueInterface $skuValue,
        ProductUniqueDataInterface $uniqueData
    ) {
        $repository->deleteUniqueDataForProduct($product)->shouldBeCalled();

        $product->getValues()->willReturn($values);
        $values->getUniqueValues()->willReturn([$skuValue]);
        $factory->create($product, $skuValue)->willReturn($uniqueData);
        $uniqueDataCollection = new ArrayCollection([$uniqueData]);
        $product->setUniqueData(Argument::type('Doctrine\Common\Collections\ArrayCollection'))->shouldBeCalled();

        $this->synchronize($product);
    }
}
