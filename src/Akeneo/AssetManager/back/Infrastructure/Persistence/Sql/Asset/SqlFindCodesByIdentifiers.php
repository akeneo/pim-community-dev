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
use Doctrine\DBAL\Connection;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class SqlFindCodesByIdentifiers implements FindCodesByIdentifiersInterface
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
    public function find(array $identifiers): array
    {
        $query = <<<SQL
        SELECT identifier, code
        FROM akeneo_reference_entity_record
        WHERE identifier IN (:identifiers)
SQL;

        $statement = $this->sqlConnection->executeQuery(
            $query,
            ['identifiers' => $identifiers],
            ['identifiers' => Connection::PARAM_STR_ARRAY]
        );

        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $indexedCodes = [];
        foreach ($results as $result) {
            $indexedCodes[$result['identifier']] = $result['code'];
        }

        return $indexedCodes;
    }
}
