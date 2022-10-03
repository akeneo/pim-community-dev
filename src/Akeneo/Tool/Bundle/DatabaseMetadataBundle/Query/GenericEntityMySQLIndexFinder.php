<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\Query;

use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Factory\IndexResultsFactory;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Model\EntityIndexConfiguration;
use Doctrine\DBAL\Connection;
use Traversable;

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class GenericEntityMySQLIndexFinder implements GenericEntityIndexFinderInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function findAllByOrder(EntityIndexConfiguration $entityIndexConfiguration): Traversable
    {
        $request = $this->connection->createQueryBuilder()
            ->select($entityIndexConfiguration->getColumnsName())
            ->from($entityIndexConfiguration->getTableName());

        if ($entityIndexConfiguration->getFilterFieldName() !== null) {
            $request->where($entityIndexConfiguration->getFilterFieldName());
        }

        $request->orderBy($entityIndexConfiguration->getIdentifierFieldName(), 'ASC');

        $results = $request->executeQuery()->iterateAssociative();

        $resultsData = [];
        foreach ($results as $result) {
            if ($entityIndexConfiguration->getDateFieldName() !== null) {
                $dateField = $entityIndexConfiguration->getDateFieldName();
                $dateFormat = $entityIndexConfiguration->getDataProcessing();
                $result[$dateField] = $dateFormat($result[$dateField]);

                $resultsData[] = IndexResultsFactory::initIndexFormatDataResults($result[$entityIndexConfiguration->getIdentifierFieldName()], $entityIndexConfiguration->getDateFieldName()?$result[$entityIndexConfiguration->getDateFieldName()]:null);
            }
        }
        sort($resultsData);

        return new \ArrayIterator($resultsData);
    }
}
