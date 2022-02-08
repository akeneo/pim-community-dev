<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Infrastructure\Connector\Processor;

use Akeneo\Platform\TailoredImport\Application\Common\DataMappingCollection;
use Akeneo\Platform\TailoredImport\Application\Common\Row;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\ExecuteDataMappingHandler;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\ExecuteDataMappingQuery;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    private StepExecution $stepExecution;
    private ?DataMappingCollection $dataMappingCollection = null;

    public function __construct(
        private ExecuteDataMappingHandler $handler
    ){}

    public function process($item)
    {
        if (!$item instanceof Row) {
            throw new \RuntimeException('Invalid type of item');
        }

        $query = new ExecuteDataMappingQuery($item, $this->getDataMappingCollection());
        return $this->handler->handle($query);
    }

    private function getDataMappingCollection(): DataMappingCollection
    {
        if (null === $this->dataMappingCollection) {
            $this->dataMappingCollection = DataMappingCollection::createFromNormalized($this->stepExecution->getJobParameters()->get('import_structure')['data_mappings']);
        }
        return $this->dataMappingCollection;
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}