<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\SubscriptionProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\SubscribeProductCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\SubscribeProductHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Command\CreateProposalCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Command\CreateProposalHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Write\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedData;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\Family;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SubscribeProductHandlerSpec extends ObjectBehavior
{
    public function let(
        ProductRepositoryInterface $productRepository,
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        SubscriptionProviderInterface $subscriptionProvider,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        CreateProposalHandler $createProposalHandler
    ): void {
        $this->beConstructedWith(
            $productRepository,
            $subscriptionRepository,
            $subscriptionProvider,
            $identifiersMappingRepository,
            $createProposalHandler
        );
    }

    public function it_is_a_subscribe_product_handler(): void
    {
        $this->shouldHaveType(SubscribeProductHandler::class);
    }

    public function it_throws_an_exception_if_the_product_does_not_exist(
        $productRepository,
        $createProposalHandler
    ): void {
        $productId = 42;
        $productRepository->find($productId)->willReturn(null);

        $command = new SubscribeProductCommand($productId);
        $this->shouldThrow(
            new \Exception(
                sprintf('Could not find product with id "%s"', $productId)
            )
        )->during('handle', [$command]);

        $createProposalHandler->handle(Argument::cetera())->shouldNotHaveBeenCalled();
    }

    public function it_throws_an_exception_if_the_product_has_no_family(
        $productRepository,
        $createProposalHandler,
        ProductInterface $product
    ): void {
        $productId = 42;
        $productRepository->find($productId)->willReturn($product);
        $product->getFamily()->willReturn(null);

        $command = new SubscribeProductCommand($productId);
        $this->shouldThrow(
            ProductSubscriptionException::familyRequired()
        )->during('handle', [$command]);

        $createProposalHandler->handle(Argument::cetera())->shouldNotHaveBeenCalled();
    }

    public function it_throws_an_exception_if_the_product_is_already_subscribed(
        $productRepository,
        $subscriptionRepository,
        $createProposalHandler,
        ProductInterface $product,
        ProductSubscription $productSubscription
    ): void {
        $productId = 42;
        $product->getId()->willReturn($productId);
        $product->getFamily()->willReturn(new Family());
        $productRepository->find($productId)->willReturn($product);

        $subscriptionRepository->findOneByProductId($productId)->willReturn($productSubscription);

        $command = new SubscribeProductCommand($productId);
        $this->shouldThrow(
            new ProductSubscriptionException(sprintf('The product with id "%d" is already subscribed', $productId))
        )->during('handle', [$command]);

        $createProposalHandler->handle(Argument::cetera())->shouldNotHaveBeenCalled();
    }

    public function it_subscribes_a_product_to_the_data_provider(
        $subscriptionProvider,
        $identifiersMappingRepository,
        $createProposalHandler,
        ProductRepositoryInterface $productRepository,
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        ProductInterface $product,
        AttributeInterface $ean,
        ValueInterface $eanValue
    ): void {
        $productId = 42;
        $subscriptionId = uniqid();
        $suggestedValues = [[
            'pimAttributeCode' => 'foo',
            'value' => 'bar',
        ]];
        $suggestedData = new SuggestedData($suggestedValues);

        $product->getId()->willReturn($productId);
        $product->getFamily()->willReturn(new Family());
        $product->getValue('ean')->willReturn($eanValue);
        $productRepository->find($productId)->willReturn($product);

        $ean->getCode()->willReturn('ean');
        $eanValue->hasData()->willReturn(true);
        $eanValue->__toString()->willReturn('an_ean');

        $identifiersMapping = new IdentifiersMapping();
        $identifiersMapping->map('upc', $ean->getWrappedObject());
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

        $this->handle(new SubscribeProductCommand($productId));
    }
}
