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

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Processor\UnsubscriptionProcessor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class UnsubscriptionProcessorSpec extends ObjectBehavior
{
    public function it_is_an_item_processor(): void
    {
        $this->shouldImplement(ItemProcessorInterface::class);
    }

    public function it_is_an_unsubscription_processor(): void
    {
        $this->shouldHaveType(UnsubscriptionProcessor::class);
    }

    public function it_processes_products(ProductInterface $product): void
    {
        $this->process($product)->shouldReturn($product);
    }
}
