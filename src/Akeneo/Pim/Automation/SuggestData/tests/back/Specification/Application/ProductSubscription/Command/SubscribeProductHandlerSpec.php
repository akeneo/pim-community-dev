<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\SubscribeProductCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\SubscribeProductHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\Family;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SubscribeProductHandlerSpec extends ObjectBehavior
{
    public function let(
        ProductRepositoryInterface $productRepository,
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        DataProviderFactory $dataProviderFactory
    ) {
        $this->beConstructedWith(
            $productRepository,
            $subscriptionRepository,
            $dataProviderFactory
        );
    }

    public function it_is_a_subscribe_product_handler()
    {
        $this->shouldHaveType(SubscribeProductHandler::class);
    }

    public function it_throws_an_exception_if_the_product_does_not_exist($productRepository)
    {
        $productId = 42;
        $productRepository->find($productId)->willReturn(null);

        $command = new SubscribeProductCommand($productId);
        $this->shouldThrow(
            new \Exception(
                sprintf('Could not find product with id "%s"', $productId)
            )
        )->during('handle', [$command]);
    }

    public function it_throws_an_exception_if_the_product_has_no_family(
        $productRepository,
        ProductInterface $product
    ) {
        $productId = 42;
        $productRepository->find($productId)->willReturn($product);
        $product->getFamily()->willReturn(null);

        $command = new SubscribeProductCommand($productId);
        $this->shouldThrow(
            new ProductSubscriptionException('Cannot subscribe a product without family')
        )->during('handle', [$command]);
    }

    public function it_throws_an_exception_if_the_product_is_already_subscribed(
        $productRepository,
        $subscriptionRepository,
        ProductInterface $product,
        ProductSubscription $productSubscription
    ) {
        $productId = 42;
        $product->getId()->willReturn($productId);
        $product->getFamily()->willReturn(new Family());
        $productRepository->find($productId)->willReturn($product);

        $subscriptionRepository->findOneByProductId($productId)->willReturn($productSubscription);

        $command = new SubscribeProductCommand($productId);
        $this->shouldThrow(
            new ProductSubscriptionException(sprintf('The product with id "%d" is already subscribed', $productId))
        )->during('handle', [$command]);
    }

    public function it_subscribes_a_product_to_the_data_provider(
        ProductRepositoryInterface $productRepository,
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        DataProviderFactory $dataProviderFactory,
        DataProviderInterface $dataProvider,
        ProductInterface $product
    ) {
        $productId = 42;
        $product->getId()->willReturn($productId);
        $product->getFamily()->willReturn(new Family());
        $productRepository->find($productId)->willReturn($product);

        $subscriptionRepository->findOneByProductId($productId)->willReturn(null);

        $dataProviderFactory->create()->willReturn($dataProvider);
        $response = new ProductSubscriptionResponse(42, 'test-id', []);
        $dataProvider->subscribe(Argument::type(ProductSubscriptionRequest::class))->willReturn($response);

        $subscriptionRepository->save(Argument::type(ProductSubscription::class))->shouldBeCalled();

        $command = new SubscribeProductCommand($productId);
        $this->handle($command);
    }
}
