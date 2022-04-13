<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\SampleData\GeneratePreviewData;


use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier\OperationApplier;
use Akeneo\Platform\TailoredImport\Domain\Hydrator\OperationCollectionHydratorInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GeneratePreviewDataHandler
{
    public function __construct(
        private OperationApplier $operationApplier,
        private OperationCollectionHydratorInterface $operationCollectionHydrator,
    ) {
    }

    public function handle(GeneratePreviewDataQuery $getRefreshedSampleDataQuery): GeneratePreviewDataResult
    {
        $previewData = [];
        $operations = $this->operationCollectionHydrator->hydrate($getRefreshedSampleDataQuery->operations);
        foreach ($getRefreshedSampleDataQuery->sampleData as $sampleData) {
            $previewData[] = null === $sampleData ?  null : $this->operationApplier->applyOperations($operations, $sampleData);
        }

        return GeneratePreviewDataResult::create($previewData);
    }
}
