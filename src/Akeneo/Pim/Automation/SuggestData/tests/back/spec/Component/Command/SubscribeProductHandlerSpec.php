<?php

declare(strict_types=1);

namespace spec\Akeneo\Pim\Automation\SuggestData\Component\Command;

use Akeneo\Pim\Automation\SuggestData\Component\Command\SubscribeProduct;
use Akeneo\Pim\Automation\SuggestData\Component\Command\SubscribeProductHandler;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;

class SubscribeProductHandlerSpec extends ObjectBehavior
{
    function let(ProductRepositoryInterface $productRepository)
    {
        $this->beConstructedWith($productRepository);
    }

    function it_is_a_subscribe_product_handler()
    {
        $this->shouldHaveType(SubscribeProductHandler::class);
    }

    function it_throws_an_exception_if_the_product_does_not_exist($productRepository, SubscribeProduct $command)
    {
        $productId = 42;
        $command->getProductId()->willReturn($productId);
        $productRepository->find($productId)->willReturn(null);

        $this->shouldThrow(
            new \Exception(
                sprintf('Could not find product with id "%s"', $productId)
            )
        )->during('handle', [$command]);
    }
}
