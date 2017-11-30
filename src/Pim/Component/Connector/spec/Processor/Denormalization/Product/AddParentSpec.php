<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization\Product;

use Pim\Component\Catalog\EntityWithFamily\CreateVariantProduct;
use Pim\Component\Catalog\EntityWithFamily\Event\ParentHasBeenAddedToProduct;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Connector\Processor\Denormalization\Product\AddParent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AddParentSpec extends ObjectBehavior
{
    function let(
        ProductModelRepositoryInterface $productModelRepository,
        CreateVariantProduct $createVariantProduct,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith($productModelRepository, $createVariantProduct, $eventDispatcher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddParent::class);
    }

    function it_adds_a_parent_to_a_product_only_when_we_update_product(
        $productModelRepository,
        $createVariantProduct,
        $eventDispatcher,
        ProductInterface $product,
        VariantProductInterface $variantProduct,
        ProductModelInterface $productModel
    ) {
        $product->getId()->willReturn(40);

        $productModelRepository->findOneByIdentifier('parent')->willReturn()->willReturn($productModel);

        $createVariantProduct->from($product, $productModel)->willReturn($variantProduct);
        $eventDispatcher->dispatch(ParentHasBeenAddedToProduct::EVENT_NAME, Argument::type(ParentHasBeenAddedToProduct::class))
            ->shouldBeCalled();

        $this->to($product, 'parent')->shouldReturn($variantProduct);
    }

    function it_does_not_add_any_parent_to_a_product_when_we_create_a_product(
        $productModelRepository,
        $createVariantProduct,
        $eventDispatcher,
        ProductInterface $product
    ) {
        $product->getId()->willReturn(null);

        $productModelRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();
        $createVariantProduct->from(Argument::cetera())->shouldNotBeCalled();
        $eventDispatcher->dispatch(ParentHasBeenAddedToProduct::EVENT_NAME, Argument::type(ParentHasBeenAddedToProduct::class))
            ->shouldNotBeCalled();

        $this->to($product, '')->shouldReturn($product);
    }


    function it_does_not_add_any_parent_to_a_product_when_the_parent_code_is_invalid(
        $productModelRepository,
        ProductInterface $product
    ) {
        $product->getId()->willReturn(40);

        $productModelRepository->findOneByIdentifier('invalid_parent_code')->willReturn()->willReturn(null);

        $this->shouldThrow(\InvalidArgumentException::class)->during('to', [$product, 'invalid_parent_code']);
    }
}
