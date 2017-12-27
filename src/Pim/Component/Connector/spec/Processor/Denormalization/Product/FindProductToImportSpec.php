<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization\Product;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Connector\Processor\Denormalization\Product\FindProductToImport;
use Prophecy\Argument;

class FindProductToImportSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $productRepository,
        ProductBuilderInterface $productBuilder,
        ProductBuilderInterface $variantProductBuilder
    ) {
        $this->beConstructedWith(
            $productRepository,
            $productBuilder,
            $variantProductBuilder
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

        $this->fromFlatData('product_identifier', 'family', '')->shouldReturn($product);
    }

    function it_finds_variant_product_from_flat_data_given_by_the_reader(
        $productRepository,
        ProductInterface $variantProduct
    ) {
        $productRepository->findOneByIdentifier('product_identifier')->willReturn($variantProduct);

        $this->fromFlatData('product_identifier', 'family', 'parent_code')->shouldReturn($variantProduct);
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

    function it_creates_variant_product_from_flat_data_given_by_the_reader(
        $productRepository,
        $variantProductBuilder,
        ProductInterface $variantProduct
    ) {
        $productRepository->findOneByIdentifier('product_identifier')->willReturn(null);
        $variantProductBuilder->createProduct('product_identifier', 'family')->willReturn($variantProduct);

        $this->fromFlatData('product_identifier', 'family', 'parent_code')->shouldReturn($variantProduct);
    }
}
