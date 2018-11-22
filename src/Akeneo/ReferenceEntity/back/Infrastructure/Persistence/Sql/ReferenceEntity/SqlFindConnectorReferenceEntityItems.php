<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\FindConnectorReferenceEntityItemsInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityQuery;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\ReferenceEntity\Hydrator\ConnectorReferenceEntityHydrator;
use Doctrine\DBAL\Connection;

/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindConnectorReferenceEntityItems implements FindConnectorReferenceEntityItemsInterface
{
    /** @var Connection */
    private $connection;

    /** @var ConnectorReferenceEntityHydrator */
    private $referenceEntityHydrator;

    public function __construct(
        Connection $connection,
        ConnectorReferenceEntityHydrator $hydrator
    ) {
        $this->connection = $connection;
        $this->referenceEntityHydrator = $hydrator;
    }

    public function __invoke(ReferenceEntityQuery $query): array
    {
        $sql = <<<SQL
        SELECT
            re.identifier,
            re.labels,
            fi.file_key as image_file_key,
            fi.original_filename as image_original_filename
        FROM akeneo_reference_entity_reference_entity as re
        LEFT JOIN akeneo_file_storage_file_info AS fi ON fi.file_key = re.image
        %s
        ORDER BY identifier ASC
        LIMIT :search_after_limit
SQL;
        $sql = $this->queryIsFirstPage($query) ?
            sprintf($sql, '') :
            sprintf($sql, 'WHERE re.identifier > :search_after_identifier');

        $statement = $this->connection->executeQuery(
            $sql,
            [
                'search_after_identifier' => $query->getSearchAfterIdentifier(),
                'search_after_limit' => $query->getSize()
            ],
            [
                'search_after_identifier' => \PDO::PARAM_STR,
                'search_after_limit' => \PDO::PARAM_INT
            ]
        );

        $results = $statement->fetchAll();

        if (empty($results)) {
            return [];
        }

        $hydratedReferenceEntities = [];

        foreach ($results as $result) {
            $hydratedReferenceEntities[] = $this->referenceEntityHydrator->hydrate($result);
        }

        return $hydratedReferenceEntities;
    }

    private function queryIsFirstPage(ReferenceEntityQuery $query): bool
    {
        return empty($query->getSearchAfterIdentifier());
    }
}
