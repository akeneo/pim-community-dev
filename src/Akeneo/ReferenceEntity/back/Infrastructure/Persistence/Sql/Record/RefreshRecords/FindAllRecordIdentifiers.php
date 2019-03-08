<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\back\Infrastructure\Persistence\Sql\Record\RefreshRecords;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindAllRecordIdentifiers implements SelectRecordIdentifiersInterface
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function fetch(): \Iterator
    {
        $query = <<<SQL
SELECT identifier FROM akeneo_reference_entity_record;
SQL;
        $statement = $this->sqlConnection->executeQuery($query);

        while (false !== $result = $statement->fetch(\PDO::FETCH_COLUMN)) {
            yield RecordIdentifier::fromString($result);
        }
    }
}
