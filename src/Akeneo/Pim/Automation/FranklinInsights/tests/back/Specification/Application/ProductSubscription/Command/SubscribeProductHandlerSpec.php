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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Family;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Events\ProductSubscribed;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValues;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductInfosForSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Write\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\SelectProductInfosForSubscriptionQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SubscriptionId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedData;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SubscribeProductHandlerSpec extends ObjectBehavior
{
    public function let(
        SelectProductInfosForSubscriptionQueryInterface $selectProductInfosForSubscriptionQuery,
        GetProductSubscriptionStatusHandler $getProductSubscriptionStatusHandler,
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository,
        SubscriptionProviderInterface $subscriptionProvider,
        CreateProposalHandler $createProposalHandler,
        EventDispatcherInterface $eventDispatcher
    ): void {
        $this->beConstructedWith(
            $selectProductInfosForSubscriptionQuery,
            $getProductSubscriptionStatusHandler,
            $productSubscriptionRepository,
            $subscriptionProvider,
            $createProposalHandler,
            $eventDispatcher
        );
    }

    public function it_is_a_subscribe_product_handler(): void
    {
        $this->shouldHaveType(SubscribeProductHandler::class);
    }

    public function it_subscribes_a_product_to_the_data_provider(
        SelectProductInfosForSubscriptionQueryInterface $selectProductInfosForSubscriptionQuery,
        GetProductSubscriptionStatusHandler $getProductSubscriptionStatusHandler,
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository,
        SubscriptionProviderInterface $subscriptionProvider,
        CreateProposalHandler $createProposalHandler,
        EventDispatcherInterface $eventDispatcher
    ): void {
        $productId = new ProductId(42);
        $productIdentifierValues = new ProductIdentifierValues($productId, ['upc' => 'an_ean']);
        $family = new Family(new FamilyCode('a_family'), []);
        $subscriptionId = new SubscriptionId(uniqid());
        $suggestedValues = [[
            'pimAttributeCode' => 'foo',
            'value' => 'bar',
        ]];
        $suggestedData = new SuggestedData($suggestedValues);

        $selectProductInfosForSubscriptionQuery->execute($productId)->willReturn(new ProductInfosForSubscription(
            $productId, $productIdentifierValues, $family, 'a_product', false, false
        ));

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

        $response = new ProductSubscriptionResponse($productId, $subscriptionId, $suggestedValues, false, false);
        $subscriptionProvider->subscribe(Argument::type(ProductSubscriptionRequest::class))->willReturn($response);

        $productSubscription = (new ProductSubscription(
            $productId,
            $subscriptionId,
            ['upc' => 'an_ean']
        ))->setSuggestedData($suggestedData);
        $createProposalHandler->handle(new CreateProposalCommand($productSubscription))->shouldBeCalled();

        $productSubscriptionRepository->save($productSubscription)->shouldBeCalled();

        $eventDispatcher->dispatch(ProductSubscribed::EVENT_NAME, Argument::that(function ($event) use ($productSubscription) {
            return $event instanceof ProductSubscribed &&
                $productSubscription->getSubscriptionId() === $event->getProductSubscription()->getSubscriptionId();
        }))->shouldBeCalled();

        $this->handle(new SubscribeProductCommand($productId));
    }
}
