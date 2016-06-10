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
        $this->process($this->getItem())->shouldReturn($product);
    }

    function it_does_not_process_if_already_an_object($productRepository, ProductInterface $product)
    {
        $productRepository->findOneByIdentifier()->shouldNotBeCalled();
        $this->process($product)->shouldReturn($product);
    }

    function it_should_return_null_when_there_is_no_product(
        $productRepository
    ) {
        $productRepository->findOneByIdentifier('foo')->willReturn(null);
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $this->process($this->getItem())->shouldReturn(null);
    }

    function it_should_throw_a_runtime_exception_when_no_identifier_is_set()
    {
        $this->shouldThrow('\RuntimeException')->during('process', [$this->getItem()]);
    }

    protected function getItem()
    {
        return [
            'sku' => [
                [
                    'scope'  => null,
                    'locale' => null,
                    'data'   => 'foo'
                ]
            ]
        ];
    }
}
