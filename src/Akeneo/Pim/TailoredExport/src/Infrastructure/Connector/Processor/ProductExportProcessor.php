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

namespace Akeneo\Pim\TailoredExport\Infrastructure\Connector\Processor;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

class ProductExportProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    private ?StepExecution $stepExecution = null;

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        if (!$product instanceof ProductInterface) {
            throw new \Exception('Invalid argument');
        }

        if (!$this->stepExecution instanceof StepExecution) {
            throw new \Exception('Processor have not been properly initialized');
        }

        $columns = $this->stepExecution->getJobParameters()->get('columns');

        $productStandard = [];

        foreach ($columns as $column) {
            $operationSourceValues = [];

            foreach ($column['sources'] as $source) {
                $value = $product->getValue($source['code'], $source['locale'], $source['channel']);

                if (null === $value) {
                    continue;
                }

                $operationSourceValues[] = $value->getData();
            }

            $productStandard[$column['target']] = implode(' ', $operationSourceValues);
        }

        return $productStandard;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }
}
