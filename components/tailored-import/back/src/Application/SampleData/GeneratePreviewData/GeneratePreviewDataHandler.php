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

namespace Akeneo\Platform\TailoredImport\Application\SampleData\GeneratePreviewData;

use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier\OperationApplier;
use Akeneo\Platform\TailoredImport\Domain\Hydrator\OperationCollectionHydratorInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ArrayValue;

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
        $sampleData = new ArrayValue($getRefreshedSampleDataQuery->sampleData);

        $previewData = $this->operationApplier->applyOperationWithIndexedResults($operations, $sampleData);

        return GeneratePreviewDataResult::create($previewData);
    }
}
