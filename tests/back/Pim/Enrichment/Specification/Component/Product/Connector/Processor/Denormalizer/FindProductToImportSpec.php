<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\FindProductToImport;
use Prophecy\Argument;

class FindProductToImportSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $productRepository,
        ProductBuilderInterface $productBuilder
    ) {
        $this->beConstructedWith(
            $productRepository,
            $productBuilder
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FindProductToImport::class);
    }

    function it_finds_product_from_flat_data_given_by_the_reader(
        $productRepository,
        ProductInterface $product
    ) {
        $productRepository->findOneByIdentifier('product_identifier')->willReturn($product);

        $this->fromFlatData('product_identifier', 'family')->shouldReturn($product);
    }

    function it_creates_product_from_flat_data_given_by_the_reader(
        $productRepository,
        $productBuilder,
        ProductInterface $product
    ) {
        $productRepository->findOneByIdentifier('product_identifier')->willReturn(null);
        $productBuilder->createProduct('product_identifier', 'family')->willReturn($product);

        $this->fromFlatData('product_identifier', 'family', '')->shouldReturn($product);
    }
}
