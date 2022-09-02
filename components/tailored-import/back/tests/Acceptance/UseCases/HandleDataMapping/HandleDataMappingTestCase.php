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

namespace Akeneo\Platform\TailoredImport\Test\Acceptance\UseCases\HandleDataMapping;

use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\ExecuteDataMappingHandler;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\ExecuteDataMappingQuery;
use Akeneo\Platform\TailoredImport\Domain\Model\DataMapping;
use Akeneo\Platform\TailoredImport\Domain\Model\DataMappingCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Row;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Infrastructure\Hydrator\OperationCollectionHydrator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class HandleDataMappingTestCase extends KernelTestCase
{
    public function setUp(): void
    {
        static::bootKernel(['debug' => false]);
    }

    protected function createIdentifierDataMapping(string $identifierColumnUuid): DataMapping
    {
        return DataMapping::create(
            'b244c45c-d5ec-4993-8cff-7ccd04e82fef',
            AttributeTarget::create(
                'sku',
                'pim_catalog_identifier',
                null,
                null,
                'set',
                'skip',
                null,
            ),
            [$identifierColumnUuid],
            OperationCollection::create([]),
            [],
        );
    }

    protected function getExecuteDataMappingHandler(): ExecuteDataMappingHandler
    {
        return self::getContainer()->get('akeneo.tailored_import.handler.execute_data_mapping');
    }

    protected function getExecuteDataMappingQuery(array $row, string $uuid, array $dataMappings): ExecuteDataMappingQuery
    {
        $operationCollectionHydrator = new OperationCollectionHydrator();
        $dataMappings = \array_map(
            static fn (DataMapping $dataMapping) => $dataMapping::create(
                $dataMapping->getUuid(),
                $dataMapping->getTarget(),
                $dataMapping->getSources(),
                $operationCollectionHydrator->hydrate(
                    $dataMapping->getTarget()->normalize(),
                    $dataMapping->getOperations()->normalize(),
                ),
                $dataMapping->getSampleData()
            ),
            $dataMappings
        );

        return new ExecuteDataMappingQuery(
            1,
            new Row($row),
            DataMappingCollection::create([
                $this->createIdentifierDataMapping($uuid),
                ...$dataMappings,
            ]),
        );
    }
}
