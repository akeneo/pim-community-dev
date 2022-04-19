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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Hydrator;

use Akeneo\Platform\TailoredImport\Domain\Model\DataMapping;
use Akeneo\Platform\TailoredImport\Domain\Model\DataMappingCollection;

class DataMappingCollectionHydrator
{
    public function __construct(
        private TargetHydrator $targetHydrator,
        private OperationCollectionHydrator $operationCollectionHydrator,
    ) {
    }

    public function hydrate(array $normalizedDataMappingCollection, array $indexedAttributes): DataMappingCollection
    {
        $dataMappingCollection = array_map(
            fn (array $dataMapping) => DataMapping::create(
                $dataMapping['uuid'],
                $this->targetHydrator->hydrate($dataMapping['target'], $indexedAttributes),
                $dataMapping['sources'],
                $this->operationCollectionHydrator->hydrate($dataMapping['operations']),
                $dataMapping['sample_data'],
            ),
            $normalizedDataMappingCollection,
        );

        return DataMappingCollection::create($dataMappingCollection);
    }
}
