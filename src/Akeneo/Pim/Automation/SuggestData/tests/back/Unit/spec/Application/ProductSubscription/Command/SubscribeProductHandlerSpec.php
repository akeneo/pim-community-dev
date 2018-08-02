<?php

declare(strict_types=1);

namespace spec\Akeneo\Pim\Automation\SuggestData\Component\Command;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\SubscribeProductCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\SubscribeProductHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Prophecy\Argument;

class SubscribeProductHandlerSpec extends ObjectBehavior
{
    public function let(
        ProductRepositoryInterface $productRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        DataProviderFactory $dataProviderFactory
    ) {
        $this->beConstructedWith(
            $productRepository,
            $identifiersMappingRepository,
            $subscriptionRepository,
            $dataProviderFactory
        );
    }

    public function it_is_a_subscribe_product_handler()
    {
        $this->shouldHaveType(SubscribeProductHandler::class);
    }

    public function it_throws_an_exception_if_the_product_does_not_exist(
        $productRepository,
        $identifiersMappingRepository,
        SubscribeProductCommand $command,
        IdentifiersMapping $identifiersMapping
    ) {
        $identifiersMappingRepository->find()->willReturn($identifiersMapping);
        $identifiersMapping->isEmpty()->willReturn(false);

        $productId = 42;
        $command->getProductId()->willReturn($productId);
        $productRepository->find($productId)->willReturn(null);

        $this->shouldThrow(
            new \Exception(
                sprintf('Could not find product with id "%s"', $productId)
            )
        )->during('handle', [$command]);
    }

    public function it_throws_an_exception_if_the_identifiers_mapping_is_empty(
        $identifiersMappingRepository,
        SubscribeProductCommand $command,
        IdentifiersMapping $identifierMapping
    ) {
        $identifiersMappingRepository->find()->willReturn($identifierMapping);
        $identifierMapping->isEmpty()->willReturn(true);

        $this
            ->shouldThrow(new \Exception('Identifiers mapping has not identifier defined'))
            ->during('handle', [$command]);
    }

    public function it_subscribes_a_product_to_the_data_provider(
        ProductRepositoryInterface $productRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        DataProviderFactory $dataProviderFactory,
        DataProviderInterface $dataProvider,
        ProductInterface $product,
        SubscribeProductCommand $command
    ) {
        $identifiersMapping = new IdentifiersMapping(['foo' => 'bar']);
        $identifiersMappingRepository->find()->willReturn($identifiersMapping);

        $product->getId()->willReturn(42);
        $productRepository->find(42)->willReturn($product);

        $command->getProductId()->willReturn(42);

        $subscriptionRepository->findOneByProductAndSubscriptionId($product, 'test-id')->willReturn(null);

        $dataProviderFactory->create()->willReturn($dataProvider);
        $response = new ProductSubscriptionResponse($product->getWrappedObject(), 'test-id', []);
        $dataProvider->subscribe(Argument::type(ProductSubscriptionRequest::class))->willReturn($response);

        $subscriptionRepository->save(Argument::type(ProductSubscription::class))->shouldBeCalled();

        $this->handle($command);
    }
}
