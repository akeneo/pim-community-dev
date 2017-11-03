<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization\Product;

use Pim\Component\Catalog\EntityWithFamily\CreateVariantProduct;
use Pim\Component\Catalog\EntityWithFamily\Query;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Connector\Processor\Denormalization\Product\AddParent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AddParentSpec extends ObjectBehavior
{
    function let(
        ProductModelRepositoryInterface $productModelRepository,
        CreateVariantProduct $createVariantProduct,
        Query\TurnProduct $turnProductQuery
    ) {
        $this->beConstructedWith($productModelRepository, $createVariantProduct, $turnProductQuery);
    }

    function it is initializable()
    {
        $this->shouldHaveType(AddParent::class);
    }

    function it adds a parent to a product only when we update product(
        $productModelRepository,
        $createVariantProduct,
        $turnProductQuery,
        ProductInterface $product,
        VariantProductInterface $variantProduct,
        ProductModelInterface $productModel
    ) {
        $product->getId()->willReturn(40);

        $productModelRepository->findOneByIdentifier('parent')->willReturn()->willReturn($productModel);

        $createVariantProduct->from($product, $productModel)->willReturn($variantProduct);
        $turnProductQuery->into($variantProduct)->shouldBeCalled();

        $this->to($product, 'parent')->shouldReturn($variantProduct);
    }

    function it does not add any parent to a product when we create a product(
        $productModelRepository,
        $createVariantProduct,
        $turnProductQuery,
        ProductInterface $product
    ) {
        $product->getId()->willReturn(null);

        $productModelRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();
        $createVariantProduct->from(Argument::cetera())->shouldNotBeCalled();
        $turnProductQuery->into(Argument::cetera())->shouldNotBeCalled();

        $this->to($product, '')->shouldReturn($product);
    }

    function it does not add any parent to a product when the parent code is empty(
        $productModelRepository,
        $createVariantProduct,
        $turnProductQuery,
        ProductInterface $product
    ) {
        $product->getId()->willReturn(40);

        $productModelRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();
        $createVariantProduct->from(Argument::cetera())->shouldNotBeCalled();
        $turnProductQuery->into(Argument::cetera())->shouldNotBeCalled();

        $this->to($product, '')->shouldReturn($product);
    }

    function it does not add any parent to a product when the parent code is invalid(
        $productModelRepository,
        ProductInterface $product
    ) {
        $product->getId()->willReturn(40);

        $productModelRepository->findOneByIdentifier('invalid_parent_code')->willReturn()->willReturn(null);

        $this->shouldThrow(\InvalidArgumentException::class)->during('to', [$product, 'invalid_parent_code']);
    }
}
