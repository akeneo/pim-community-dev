<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordNotFoundException;
use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlGetReferenceEntityIdentifierForRecordIdentifier
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function __invoke(RecordIdentifier $recordIdentifier): ReferenceEntityIdentifier
    {
        $query = <<<SQL
            SELECT reference_entity_identifier
            FROM akeneo_reference_entity_record
            WHERE identifier = :identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, ['identifier' => (string) $recordIdentifier]);

        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        if (!$result) {
            throw RecordNotFoundException::withIdentifier($recordIdentifier);
        }

        return ReferenceEntityIdentifier::fromString($result['reference_entity_identifier']);
    }
}
