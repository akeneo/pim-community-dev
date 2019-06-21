<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\Writer\Database;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

class ProductAndProductModelDraftWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    /** @var ProductDraftWriter */
    private $productDraftWriter;

    /** @var ProductDraftWriter */
    private $productModelDraftWriter;

    public function __construct(
        ProductDraftWriter $productDraftWriter,
        ProductDraftWriter $productModelDraftWriter
    ) {
        $this->productDraftWriter = $productDraftWriter;
        $this->productModelDraftWriter = $productModelDraftWriter;
    }

    public function write(array $items)
    {
        $productDrafts = array_values(array_filter($items, function ($item) {
            return $item instanceof ProductDraft;
        }));
        $productModelDrafts = array_values(array_filter($items, function ($item) {
            return $item instanceof ProductModelDraft;
        }));

        if (!empty($productDrafts)) {
            $this->productDraftWriter->write($productDrafts);
        }
        if (!empty($productModelDrafts)) {
            $this->productModelDraftWriter->write($productModelDrafts);
        }
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->productDraftWriter->setStepExecution($stepExecution);
        $this->productModelDraftWriter->setStepExecution($stepExecution);
    }
}
