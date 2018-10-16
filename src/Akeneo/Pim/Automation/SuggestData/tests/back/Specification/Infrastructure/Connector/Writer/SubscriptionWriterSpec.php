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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\Writer;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\Writer\SubscriptionWriter;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SubscriptionWriterSpec extends ObjectBehavior
{
    public function let(StepExecution $stepExecution): void
    {
        $this->setStepExecution($stepExecution);
    }

    public function it_is_an_item_writer(): void
    {
        $this->shouldImplement(ItemWriterInterface::class);
    }

    public function it_is_step_execution_aware(): void
    {
        $this->shouldImplement(StepExecutionAwareInterface::class);
    }

    public function it_is_a_subscription_writer(): void
    {
        $this->shouldHaveType(SubscriptionWriter::class);
    }

    public function it_subscribes_items($stepExecution): void
    {
        $items = [
            new ProductSubscriptionRequest(new Product()),
            new ProductSubscriptionRequest(new Product()),
        ];
        $stepExecution->incrementSummaryInfo('subscribed')->shouldBeCalledTimes(2);

        $this->write($items)->shouldReturn(null);
    }
}
