<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Connector\Processor;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\ExecuteDataMappingHandler;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\ExecuteDataMappingQuery;
use Akeneo\Platform\TailoredImport\Domain\Model\DataMappingCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Infrastructure\Connector\RowPayload;
use Akeneo\Platform\TailoredImport\Infrastructure\Hydrator\DataMappingCollectionHydrator;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

class ProductProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    private ?StepExecution $stepExecution = null;
    private ?DataMappingCollection $dataMappingCollection = null;

    public function __construct(
        private ExecuteDataMappingHandler $executeDataMappingHandler,
        private GetAttributes $getAttributes,
        private DataMappingCollectionHydrator $dataMappingHydrator,
    ) {
    }

    public function process($item)
    {
        if (!$item instanceof RowPayload) {
            throw new \RuntimeException('Invalid type of item');
        }

        $query = new ExecuteDataMappingQuery($item->getRow(), $this->getDataMappingCollection());
        $upsertProductCommand = $this->executeDataMappingHandler->handle($query);
        $item->setUpsertProductCommand($upsertProductCommand);

        return $item;
    }

    private function getDataMappingCollection(): DataMappingCollection
    {
        if (null === $this->dataMappingCollection) {
            if (!$this->stepExecution instanceof StepExecution) {
                throw new \LogicException('Processor has not been properly initialized');
            }

            $normalizedDataMappings = $this->stepExecution->getJobParameters()->get('import_structure')['data_mappings'];
            $indexedAttributes = $this->getIndexedAttributes($normalizedDataMappings);
            $this->dataMappingCollection = $this->dataMappingHydrator->hydrate($normalizedDataMappings, $indexedAttributes);
        }

        return $this->dataMappingCollection;
    }

    private function getIndexedAttributes(array $dataMappings): array
    {
        $attributeCodes = [];
        foreach ($dataMappings as $dataMapping) {
            if (AttributeTarget::TYPE === $dataMapping['target']['type']) {
                $attributeCodes[] = $dataMapping['target']['code'];
            }
        }

        return array_filter($this->getAttributes->forCodes(array_unique($attributeCodes)));
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }
}
