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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Family;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValues;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductInfosForSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Write\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\SelectProductInfosForSubscriptionQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Processor\SubscriptionProcessor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
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
        SelectProductInfosForSubscriptionQueryInterface $selectProductInfosForSubscriptionQuery
    ): void {
        $this->beConstructedWith($getProductSubscriptionStatusHandler, $selectProductInfosForSubscriptionQuery);
    }

    public function it_is_an_item_processor(): void
    {
        $this->shouldImplement(ItemProcessorInterface::class);
    }

    public function it_is_a_subscription_processor(): void
    {
        $this->shouldHaveType(SubscriptionProcessor::class);
    }

    public function it_does_not_process_an_invalid_product(
        GetProductSubscriptionStatusHandler $getProductSubscriptionStatusHandler,
        SelectProductInfosForSubscriptionQueryInterface $selectProductInfosForSubscriptionQuery,
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

        $selectProductInfosForSubscriptionQuery->execute(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(InvalidItemException::class)->during('process', [$product]);
    }

    public function it_successfully_processes_a_product(
        GetProductSubscriptionStatusHandler $getProductSubscriptionStatusHandler,
        SelectProductInfosForSubscriptionQueryInterface $selectProductInfosForSubscriptionQuery,
        ProductInterface $product
    ): void {
        $product->getId()->willReturn(42);
        $productId = new ProductId(42);

        $getProductSubscriptionStatusHandler->handle(new GetProductSubscriptionStatusQuery($productId))->willReturn(
            new ProductSubscriptionStatus(
                new ConnectionStatus(true, false, true, 0),
                false,
                true,
                true,
                false
            )
        );

        $selectProductInfosForSubscriptionQuery->execute($productId)->willReturn(new ProductInfosForSubscription(
            $productId,
            new ProductIdentifierValues($productId, ['asin' => '123456']),
            new Family(new FamilyCode('a_family'), []),
            'foobar',
            false,
            false
        ));

        $this->process($product)->shouldReturnAnInstanceOf(ProductSubscriptionRequest::class);
    }
}
