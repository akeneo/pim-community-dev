<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Infrastructure\Connector\Processor;

use Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent\SetTextValue;
use Akeneo\Platform\TailoredImport\Application\Common\DataMapping;
use Akeneo\Platform\TailoredImport\Application\Common\DataMappingCollection;
use Akeneo\Platform\TailoredImport\Application\Common\Row;
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

    public function process($item): array
    {
        if (!$item instanceof Row) {
            throw new \RuntimeException('Invalid type of item');
        }

        $dataMappingCollection = DataMappingCollection::createFromNormalized($this->stepExecution->getJobParameters()->get('import_structure')['data_mappings']);

        $userIntents = [];

        /** @var DataMapping $dataMapping */
        foreach ($dataMappingCollection->getIterator() as $dataMapping) {
            $cellData = implode("", array_map(static fn(string $uuid) => $item->getCellData($uuid), $dataMapping->getSources()));
            /** TODO Iterate over operation */
            $target = $dataMapping->getTarget();
            $userIntents[] = new SetTextValue($target->getCode(), $target->getLocale(), $target->getChannel(), $cellData);
        }

        return $userIntents;
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}