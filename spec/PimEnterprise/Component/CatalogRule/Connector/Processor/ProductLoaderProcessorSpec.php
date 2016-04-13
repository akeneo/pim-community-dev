<?php

namespace spec\PimEnterprise\Component\CatalogRule\Connector\Processor;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;

class ProductLoaderProcessorSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $productRepository)
    {
        $this->beConstructedWith($productRepository);
    }

    function it_should_process(
        $productRepository,
        ProductInterface $product
    ) {
        $productRepository->findOneByIdentifier('foo')->willReturn($product);
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $this->process(['sku' => 'foo'])->shouldReturn($product);
    }

    function it_should_return_null_when_there_is_no_product(
        $productRepository
    ) {
        $productRepository->findOneByIdentifier('foo')->willReturn(null);
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $this->process(['sku' => 'foo'])->shouldReturn(null);
    }

    function it_should_throw_a_runtime_exception_when_no_identifier_is_set()
    {
        $this->shouldThrow('\RuntimeException')->during('process', [['sku' => 'foo']]);
    }
}
