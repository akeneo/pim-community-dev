<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\TailoredExport\Infrastructure\Connector\Processor;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class ProductExportProcessorSpec extends ObjectBehavior
{
    function let(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution);
    }

    public function it_process_export(ProductInterface $product)
    {
        $product->getId()->willReturn(12);

        $this->process($product)->shouldReturn(['id' => 12]);
    }
}
