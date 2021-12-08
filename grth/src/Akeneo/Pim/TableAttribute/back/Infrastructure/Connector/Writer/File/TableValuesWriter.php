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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Connector\Writer\File;

use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\TableValuesTranslator;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

final class TableValuesWriter implements ItemWriterInterface, InitializableInterface, FlushableInterface, StepExecutionAwareInterface
{
    private StepExecution $stepExecution;

    public function __construct(
        private ItemWriterInterface $decoratedWriter,
        private TableValuesTranslator $tableValuesTranslator
    ) {
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
        if ($this->decoratedWriter instanceof StepExecutionAwareInterface) {
            $this->decoratedWriter->setStepExecution($stepExecution);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items): void
    {
        $parameters = $this->stepExecution->getJobParameters();

        if ($parameters->has('with_label') && $parameters->get('with_label') && $parameters->has('file_locale')) {
            $fileLocale = $parameters->get('file_locale');
            $headerWithLabel = $parameters->has('header_with_label') && $parameters->get('header_with_label');

            $items = $this->tableValuesTranslator->translate($items, $fileLocale, $headerWithLabel);
        }

        $this->decoratedWriter->write($items);
    }

    public function flush()
    {
        if ($this->decoratedWriter instanceof FlushableInterface) {
            $this->decoratedWriter->flush();
        }
    }

    public function initialize()
    {
        if ($this->decoratedWriter instanceof InitializableInterface) {
            $this->decoratedWriter->initialize();
        }
    }
}
