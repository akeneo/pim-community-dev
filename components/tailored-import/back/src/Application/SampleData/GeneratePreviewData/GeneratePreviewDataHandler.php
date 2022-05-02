<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\SampleData\GeneratePreviewData;

use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier\OperationApplier;
use Akeneo\Platform\TailoredImport\Domain\Hydrator\OperationCollectionHydratorInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;

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
        $operations = $this->operationCollectionHydrator->hydrate($getRefreshedSampleDataQuery->target, $getRefreshedSampleDataQuery->operations);
        $previewData = [];
        foreach ($getRefreshedSampleDataQuery->sampleData as $sampleData) {
            $previewData[] = null === $sampleData ? null : $this->operationApplier->applyOperations($operations, new StringValue($sampleData))->getValue();
        }

        return GeneratePreviewDataResult::create($previewData);
    }
}
