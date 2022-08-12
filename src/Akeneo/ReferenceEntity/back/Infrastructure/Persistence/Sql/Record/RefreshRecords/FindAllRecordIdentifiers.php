<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\RefreshRecords;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindAllRecordIdentifiers implements SelectRecordIdentifiersInterface
{
    public function __construct(
        private Connection $sqlConnection,
        private int $batchSize
    ) {
    }

    public function fetch(): \Iterator
    {
        $searchAfterIdentifier = null;

        $query = <<<SQL
           SELECT identifier
           FROM akeneo_reference_entity_record
           %s
           ORDER BY identifier
           LIMIT :search_after_limit;
SQL;

        while (true) {
            $sql = $searchAfterIdentifier === null ?
                sprintf($query, '') :
                sprintf($query, 'WHERE identifier > :search_after_identifier');

            $statement = $this->sqlConnection->executeQuery(
                $sql,
                [
                    'search_after_identifier' => $searchAfterIdentifier,
                    'search_after_limit' => $this->batchSize
                ],
                [
                    'search_after_limit' => \PDO::PARAM_INT
                ]
            );

            if ($statement->rowCount() === 0) {
                return;
            }

            while (false !== $result = $statement->fetchOne()) {
                yield RecordIdentifier::fromString($result);
                $searchAfterIdentifier = $result;
            }
        }
    }
}
