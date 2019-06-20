<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindIdentifiersByReferenceEntityAndCodesInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class SqlFindIdentifiersByReferenceEntityAndCodes implements FindIdentifiersByReferenceEntityAndCodesInterface
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function find(ReferenceEntityIdentifier $referenceEntityIdentifier, array $recordCodes): array
    {
        $query = <<<SQL
        SELECT identifier, code
        FROM akeneo_reference_entity_record
        WHERE reference_entity_identifier = :referenceEntityIdentifier
        AND code IN (:codes)
SQL;

        $statement = $this->sqlConnection->executeQuery(
            $query,
            [
                'referenceEntityIdentifier' => (string) $referenceEntityIdentifier,
                'codes' => $recordCodes
            ],
            [
                'codes' => Connection::PARAM_STR_ARRAY
            ]
        );


        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $identifiers = [];
        foreach ($results as $result) {
            $identifiers[$result['code']] = RecordIdentifier::fromString($result['identifier']);
        }

        return $identifiers;
    }
}
