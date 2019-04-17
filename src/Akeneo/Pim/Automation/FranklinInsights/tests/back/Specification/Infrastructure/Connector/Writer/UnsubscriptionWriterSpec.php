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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Writer;

use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UnsubscribeProductCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UnsubscribeProductHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Writer\UnsubscriptionWriter;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class UnsubscriptionWriterSpec extends ObjectBehavior
{
    public function let(UnsubscribeProductHandler $handler, StepExecution $stepExecution): void
    {
        $this->beConstructedWith($handler);
        $this->setStepExecution($stepExecution);
    }

    public function it_is_an_item_writer(): void
    {
        $this->shouldImplement(ItemWriterInterface::class);
    }

    public function it_is_an_unsubscription_writer(): void
    {
        $this->shouldHaveType(UnsubscriptionWriter::class);
    }

    public function it_unsusbscribes_products(
        $handler,
        $stepExecution,
        ProductInterface $product
    ): void {
        $product->getId()->willReturn(42);
        $handler->handle(new UnsubscribeProductCommand(new ProductId(42)))->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('unsubscribed')->shouldBeCalled();

        $this->write([$product])->shouldReturn(null);
    }

    public function it_throws_an_exception_if_product_is_not_subscribed(
        $handler,
        $stepExecution,
        ProductInterface $product
    ): void {
        $product->getId()->willReturn(42);
        $product->getIdentifier()->willReturn('a-sku');

        $handler->handle(new UnsubscribeProductCommand(new ProductId(42)))->willThrow(
            new ProductSubscriptionException('Product is not subscribed')
        );

        $stepExecution->incrementSummaryInfo('unsubscribed')->shouldNotBeCalled();
        $this->shouldThrow(InvalidItemException::class)->during('write', [[$product]]);
    }
}
