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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindExistingRecordCodesInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindExistingRecordCodes implements FindExistingRecordCodesInterface
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function find(ReferenceEntityIdentifier $referenceEntityIdentifier, array $recordCodes): array
    {
        $query = <<<SQL
        SELECT code
        FROM akeneo_reference_entity_record
        WHERE reference_entity_identifier = :referenceEntityIdentifier
        AND code IN (:codes)
SQL;

        $statement = $this->sqlConnection->executeQuery($query, [
            'referenceEntityIdentifier' => (string) $referenceEntityIdentifier,
            'codes' => $recordCodes
        ],['codes' => Connection::PARAM_STR_ARRAY]);

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }
}
