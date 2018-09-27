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

use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\Writer\UnsubscriptionWriter;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class UnsubscriptionWriterSpec extends ObjectBehavior
{
    public function let(StepExecution $stepExecution): void
    {
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

    public function it_unsusbscribes_products($stepExecution)
    {
        $stepExecution->incrementSummaryInfo('unsubscribed')->shouldBeCalledTimes(2);

        $items= [
            new ProductSubscription(new Product(), 'fake-subscription-id'),
            new ProductSubscription(new Product(), 'another-fake-subscription-id'),
        ];
        $this->write($items)->shouldReturn(null);
    }
}
