<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\SubscriptionProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\SubscribeProductCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\SubscribeProductHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Query\GetProductSubscriptionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Query\GetProductSubscriptionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Command\CreateProposalCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Command\CreateProposalHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Events\ProductSubscribed;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Write\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SubscriptionId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedData;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\Family;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SubscribeProductHandlerSpec extends ObjectBehavior
{
    public function let(
        ProductRepositoryInterface $productRepository,
        GetProductSubscriptionStatusHandler $getProductSubscriptionStatusHandler,
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        SubscriptionProviderInterface $subscriptionProvider,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        CreateProposalHandler $createProposalHandler,
        EventDispatcherInterface $eventDispatcher
    ): void {
        $this->beConstructedWith(
            $productRepository,
            $getProductSubscriptionStatusHandler,
            $subscriptionRepository,
            $subscriptionProvider,
            $identifiersMappingRepository,
            $createProposalHandler,
            $eventDispatcher
        );
    }

    public function it_is_a_subscribe_product_handler(): void
    {
        $this->shouldHaveType(SubscribeProductHandler::class);
    }

    public function it_throws_an_exception_if_the_product_cannot_be_subscribed(
        $productRepository,
        $getProductSubscriptionStatusHandler,
        $createProposalHandler,
        ProductInterface $product
    ): void {
        $productId = 42;
        $productRepository->find($productId)->willReturn($product);

        $query = new GetProductSubscriptionStatusQuery($productId);
        $getProductSubscriptionStatusHandler->handle($query)->willReturn(
            new ProductSubscriptionStatus(
                new ConnectionStatus(true, true, true, 42),
                false,
                false,
                true,
                false
            )
        );

        $command = new SubscribeProductCommand($productId);
        $this->shouldThrow(
            ProductSubscriptionException::familyRequired()
        )->during('handle', [$command]);

        $createProposalHandler->handle(Argument::cetera())->shouldNotHaveBeenCalled();
    }

    public function it_subscribes_a_product_to_the_data_provider(
        $productRepository,
        $getProductSubscriptionStatusHandler,
        $subscriptionProvider,
        $subscriptionRepository,
        $identifiersMappingRepository,
        $createProposalHandler,
        $eventDispatcher,
        ProductInterface $product,
        ValueInterface $eanValue
    ): void {
        $productId = 42;
        $subscriptionId = new SubscriptionId(uniqid());
        $suggestedValues = [[
            'pimAttributeCode' => 'foo',
            'value' => 'bar',
        ]];
        $suggestedData = new SuggestedData($suggestedValues);

        $product->getId()->willReturn($productId);
        $product->getFamily()->willReturn(new Family());
        $product->getValue('ean')->willReturn($eanValue);
        $productRepository->find($productId)->willReturn($product);

        $query = new GetProductSubscriptionStatusQuery($productId);
        $getProductSubscriptionStatusHandler->handle($query)->willReturn(
            new ProductSubscriptionStatus(
                new ConnectionStatus(true, true, true, 42),
                false,
                true,
                true,
                false
            )
        );

        $eanValue->hasData()->willReturn(true);
        $eanValue->__toString()->willReturn('an_ean');

        $identifiersMapping = new IdentifiersMapping(['upc' => 'ean']);
        $identifiersMappingRepository->find()->willReturn($identifiersMapping);

        $subscriptionRepository->findOneByProductId($productId)->willReturn(null);

        $response = new ProductSubscriptionResponse($productId, $subscriptionId, $suggestedValues, false, false);
        $subscriptionProvider->subscribe(Argument::type(ProductSubscriptionRequest::class))->willReturn($response);

        $productSubscription = (new ProductSubscription(
            $productId,
            $subscriptionId,
            ['upc' => 'an_ean']
        ))->setSuggestedData($suggestedData);
        $createProposalHandler->handle(new CreateProposalCommand($productSubscription))->shouldBeCalled();

        $subscriptionRepository->save($productSubscription)->shouldBeCalled();

        $eventDispatcher->dispatch(ProductSubscribed::EVENT_NAME, Argument::that(function ($event) use ($productSubscription) {
            return $event instanceof ProductSubscribed &&
                $productSubscription->getSubscriptionId() === $event->getProductSubscription()->getSubscriptionId();
        }))->shouldBeCalled();

        $this->handle(new SubscribeProductCommand($productId));
    }
}
