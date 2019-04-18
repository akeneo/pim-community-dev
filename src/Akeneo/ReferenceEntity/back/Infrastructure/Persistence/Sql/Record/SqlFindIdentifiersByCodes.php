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

use Akeneo\ReferenceEntity\Domain\Query\Record\FindCodesByIdentifiersInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindIdentifiersByCodesInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class SqlFindIdentifiersByCodes implements FindIdentifiersByCodesInterface
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
    public function find(string $referenceEntityIdentifiers, array $codes): array
    {
        $query = <<<SQL
        SELECT identifier
        FROM akeneo_reference_entity_record
        WHERE code IN (:codes)
        AND reference_entity_identifier = :reference_entity_identifier
SQL;

        $statement = $this->sqlConnection->executeQuery(
            $query,
            ['codes' => $codes, 'reference_entity_identifier' => $referenceEntityIdentifiers],
            ['codes' => Connection::PARAM_STR_ARRAY]
        );

        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $indexedCodes = [];
        foreach ($results as $result) {
            $indexedCodes[] = $result['identifier'];
        }

        return $indexedCodes;
    }
}
