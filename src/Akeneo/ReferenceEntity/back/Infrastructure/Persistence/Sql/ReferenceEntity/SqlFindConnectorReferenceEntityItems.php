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
SQL;

        $statement = $this->connection->executeQuery(
            $sql
        );

        $results = $statement->fetchAll();

        if (empty($results)) {
            return null;
        }

        $hydratedReferenceEntities = [];

        foreach($results as $result) {
            $hydratedReferenceEntities[] = $this->referenceEntityHydrator->hydrate($result);
        }

        return $hydratedReferenceEntities;
    }
}
