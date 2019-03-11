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
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlRecordsExists
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    /**
     * @param string[] $codes
     *
     * @return string[]
     */
    public function withReferenceEntityAndCodes(ReferenceEntityIdentifier $referenceEntityIdentifier, array $codes): array
    {
        $query = <<<SQL
        SELECT code
        FROM akeneo_reference_entity_record
        WHERE reference_entity_identifier = :referenceEntityIdentifier
        AND code IN (:codes)
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $query,
            [
                'referenceEntityIdentifier' => (string) $referenceEntityIdentifier,
                'codes'                     => $codes,
            ],
            [
                'codes' => Connection::PARAM_STR_ARRAY
            ]
        );
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        return array_values($result);
    }
}
