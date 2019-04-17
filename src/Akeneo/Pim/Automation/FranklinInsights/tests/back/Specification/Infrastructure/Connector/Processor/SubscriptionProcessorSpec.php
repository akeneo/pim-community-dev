<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Processor;

use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Query\GetProductSubscriptionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Query\GetProductSubscriptionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Write\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Processor\SubscriptionProcessor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SubscriptionProcessorSpec extends ObjectBehavior
{
    public function let(
        GetProductSubscriptionStatusHandler $getProductSubscriptionStatusHandler,
        ProductRepositoryInterface $productRepository
    ): void {
        $this->beConstructedWith($getProductSubscriptionStatusHandler, $productRepository);
    }

    public function it_is_an_item_processor(): void
    {
        $this->shouldImplement(ItemProcessorInterface::class);
    }

    public function it_is_a_subscription_processor(): void
    {
        $this->shouldHaveType(SubscriptionProcessor::class);
    }

    public function it_does_not_process_a_variant_product(
        $getProductSubscriptionStatusHandler,
        $productRepository,
        ProductInterface $product
    ): void {
        $product->getId()->willReturn(42);
        $product->getIdentifier()->willReturn('foobar');

        $getProductSubscriptionStatusHandler->handle(new GetProductSubscriptionStatusQuery(new ProductId(42)))->willReturn(
            new ProductSubscriptionStatus(
                new ConnectionStatus(true, false, true, 0),
                false,
                true,
                true,
                true
            )
        );

        $productRepository->find(Argument::any())->shouldNotBeCalled();

        try {
            $this->process($product);
        } catch (\Exception $exception) {
            $this->shouldHaveThrownWithMessageAndProductIdentifier(
                $exception,
                ProductSubscriptionException::variantProduct()->getMessage(),
                'foobar'
            );
        }
    }

    public function it_does_not_process_a_product_without_family(
        $getProductSubscriptionStatusHandler,
        $productRepository,
        ProductInterface $product
    ): void {
        $product->getId()->willReturn(42);
        $product->getIdentifier()->willReturn('foobar');

        $getProductSubscriptionStatusHandler->handle(new GetProductSubscriptionStatusQuery(new ProductId(42)))->willReturn(
            new ProductSubscriptionStatus(
                new ConnectionStatus(true, false, true, 0),
                false,
                false,
                true,
                false
            )
        );

        $productRepository->find(Argument::any())->shouldNotBeCalled();

        try {
            $this->process($product);
        } catch (\Exception $exception) {
            $this->shouldHaveThrownWithMessageAndProductIdentifier(
                $exception,
                ProductSubscriptionException::familyRequired()->getMessage(),
                'foobar'
            );
        }
    }

    public function it_does_not_process_a_product_already_subscribed(
        $getProductSubscriptionStatusHandler,
        $productRepository,
        ProductInterface $product
    ): void {
        $product->getId()->willReturn(42);
        $product->getIdentifier()->willReturn('foobar');

        $getProductSubscriptionStatusHandler->handle(new GetProductSubscriptionStatusQuery(new ProductId(42)))->willReturn(
            new ProductSubscriptionStatus(
                new ConnectionStatus(true, false, true, 0),
                true,
                true,
                true,
                false
            )
        );

        $productRepository->find(Argument::any())->shouldNotBeCalled();

        try {
            $this->process($product);
        } catch (\Exception $exception) {
            $this->shouldHaveThrownWithMessageAndProductIdentifier(
                $exception,
                ProductSubscriptionException::alreadySubscribedProduct()->getMessage(),
                'foobar'
            );
        }
    }

    public function it_does_not_process_a_product_without_identifier_values(
        $getProductSubscriptionStatusHandler,
        $productRepository,
        ProductInterface $product
    ): void {
        $product->getId()->willReturn(42);
        $product->getIdentifier()->willReturn('foobar');

        $getProductSubscriptionStatusHandler->handle(new GetProductSubscriptionStatusQuery(new ProductId(42)))->willReturn(
            new ProductSubscriptionStatus(
                new ConnectionStatus(true, false, true, 0),
                false,
                true,
                false,
                false
            )
        );

        $productRepository->find(Argument::any())->shouldNotBeCalled();

        try {
            $this->process($product);
        } catch (\Exception $exception) {
            $this->shouldHaveThrownWithMessageAndProductIdentifier(
                $exception,
                ProductSubscriptionException::invalidMappedValues()->getMessage(),
                'foobar'
            );
        }
    }

    public function it_successfully_processes_a_product(
        $getProductSubscriptionStatusHandler,
        $productRepository,
        ProductInterface $product
    ): void {
        $product->getId()->willReturn(42);

        $getProductSubscriptionStatusHandler->handle(new GetProductSubscriptionStatusQuery(new ProductId(42)))->willReturn(
            new ProductSubscriptionStatus(
                new ConnectionStatus(true, false, true, 0),
                false,
                true,
                true,
                false
            )
        );

        $productRepository->find(42)->willReturn($product);

        $this->process($product)->shouldReturnAnInstanceOf(ProductSubscriptionRequest::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchers(): array
    {
        return [
            'haveThrownWithMessageAndProductIdentifier' => function (
                SubscriptionProcessor $subject,
                InvalidItemException $exception,
                string $message,
                string $identifier
            ) {
                if ($message === $exception->getMessage() &&
                    ['identifier' => $identifier] === $exception->getItem()->getInvalidData()
                ) {
                    return true;
                }

                return false;
            },
        ];
    }
}
