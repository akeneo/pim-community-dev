<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\SubscribeProductCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\SubscribeProductHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\Family;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SubscribeProductHandlerSpec extends ObjectBehavior
{
    function let(
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

    function it_is_a_subscribe_product_handler()
    {
        $this->shouldHaveType(SubscribeProductHandler::class);
    }

    function it_throws_an_exception_if_the_product_does_not_exist($productRepository)
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

    function it_throws_an_exception_if_the_product_has_no_family(
        $productRepository,
        ProductInterface $product
    ) {
        $productId = 42;
        $productRepository->find($productId)->willReturn($product);
        $product->getFamily()->willReturn(null);

        $command = new SubscribeProductCommand(42);
        $this->shouldThrow(
            new ProductSubscriptionException('Cannot subscribe a product without family')
        )->during('handle', [$command]);
    }

    function it_throws_an_exception_if_the_product_is_already_subscribed(
        $productRepository,
        $subscriptionRepository,
        ProductInterface $product
    ) {
        $productId = 42;
        $product->getId()->willReturn($productId);
        $product->getFamily()->willReturn(new Family());
        $productRepository->find(42)->willReturn($product);

        $subscriptionRepository->getSubscriptionStatusForProductId(42)->willReturn(
            ['subscription_id' => 'a-subscription-id']
        );

        $command = new SubscribeProductCommand($productId);
        $this->shouldThrow(
            new ProductSubscriptionException(sprintf('The product with id "%d" is already subscribed', $productId))
        )->during('handle', [$command]);
    }

    function it_subscribes_a_product_to_the_data_provider(
        ProductRepositoryInterface $productRepository,
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        DataProviderFactory $dataProviderFactory,
        DataProviderInterface $dataProvider,
        ProductInterface $product
    ) {
        $productId = 42;
        $product->getId()->willReturn($productId);
        $product->getFamily()->willReturn(new Family());
        $productRepository->find(42)->willReturn($product);

        $subscriptionRepository->getSubscriptionStatusForProductId(42)->willReturn(['subscription_id' => '']);

        $dataProviderFactory->create()->willReturn($dataProvider);
        $response = new ProductSubscriptionResponse($product->getWrappedObject(), 'test-id', []);
        $dataProvider->subscribe(Argument::type(ProductSubscriptionRequest::class))->willReturn($response);

        $subscriptionRepository->save(Argument::type(ProductSubscription::class))->shouldBeCalled();

        $command = new SubscribeProductCommand($productId);
        $this->handle($command);
    }
}
