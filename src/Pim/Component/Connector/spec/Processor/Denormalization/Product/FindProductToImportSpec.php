<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization\Product;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
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

    function it is initializable()
    {
        $this->shouldHaveType(FindProductToImport::class);
    }

    function it finds product from flat data given by the reader(
        $productRepository,
        ProductInterface $product
    ) {
        $productRepository->findOneByIdentifier('product_identifier')->willReturn($product);

        $this->fromFlatData('product_identifier', 'family', '')->shouldReturn($product);
    }

    function it finds variant product from flat data given by the reader(
        $productRepository,
        VariantProductInterface $variantProduct
    ) {
        $productRepository->findOneByIdentifier('product_identifier')->willReturn($variantProduct);

        $this->fromFlatData('product_identifier', 'family', 'parent_code')->shouldReturn($variantProduct);
    }

    function it creates product from flat data given by the reader(
        $productRepository,
        $productBuilder,
        ProductInterface $product
    ) {
        $productRepository->findOneByIdentifier('product_identifier')->willReturn(null);
        $productBuilder->createProduct('product_identifier', 'family')->willReturn($product);

        $this->fromFlatData('product_identifier', 'family', '')->shouldReturn($product);
    }

    function it creates variant product from flat data given by the reader(
        $productRepository,
        $variantProductBuilder,
        VariantProductInterface $variantProduct
    ) {
        $productRepository->findOneByIdentifier('product_identifier')->willReturn(null);
        $variantProductBuilder->createProduct('product_identifier', 'family')->willReturn($variantProduct);

        $this->fromFlatData('product_identifier', 'family', 'parent_code')->shouldReturn($variantProduct);
    }
}
