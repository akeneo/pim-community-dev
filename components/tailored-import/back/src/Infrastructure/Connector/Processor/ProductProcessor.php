<?php

namespace Akeneo\Platform\TailoredImport\Infrastructure\Connector\Processor;

use Akeneo\Platform\TailoredImport\Application\Common\DataMappingCollection;
use Akeneo\Platform\TailoredImport\Application\Common\Row;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

class ProductProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    private StepExecution $stepExecution;

    public function process($item)
    {
        if (!$item instanceof Row) {
            throw new \RuntimeException('Invalid type of item');
        }

        $dataMappingCollection = DataMappingCollection::createFromNormalized($this->stepExecution->getJobParameters()->get('import_structure')['data_mappings']);

    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}