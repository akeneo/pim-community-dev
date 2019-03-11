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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindValueKeyCollectionInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKey;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use Doctrine\DBAL\Connection;

class SqlFindValueKeyCollection implements FindValueKeyCollectionInterface
{
    /** @var Connection */
    private $sqlConnection;

    /** @var array */
    private $cachedResult = [];

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function __invoke(ReferenceEntityIdentifier $referenceEntityIdentifier): ValueKeyCollection
    {
        if (!isset($this->cachedResult[$referenceEntityIdentifier->normalize()])) {
            $this->cachedResult[$referenceEntityIdentifier->normalize()] = $this->fetch($referenceEntityIdentifier);
        }

        return $this->cachedResult[$referenceEntityIdentifier->normalize()];
    }

    /**
     * @param ReferenceEntityIdentifier $referenceEntityIdentifier
     *
     * @return ValueKeyCollection
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function fetch(ReferenceEntityIdentifier $referenceEntityIdentifier): ValueKeyCollection
    {
        $query = <<<SQL
            SELECT
                CONCAT(
                    mask.identifier,
                    IF(mask.value_per_channel, CONCAT('_', mask.channel_code), ''),
                    IF(mask.value_per_locale, CONCAT('_', mask.locale_code), '')
                 ) as `key`
            FROM (
                SELECT
                    a.identifier,
                    a.value_per_channel,
                    a.value_per_locale,
                    COALESCE(c.code, locale_channel.channel_code) as channel_code,
                    COALESCE(l.code, locale_channel.locale_code) as locale_code
                FROM
                    akeneo_reference_entity_attribute as a
                    LEFT JOIN pim_catalog_channel c ON value_per_channel = 1 AND value_per_locale = 0
                    LEFT JOIN pim_catalog_locale l ON value_per_channel = 0 AND value_per_locale = 1 AND is_activated = 1
                    LEFT JOIN (
                        SELECT
                            c.code as channel_code,
                            l.code as locale_code
                        FROM
                            pim_catalog_channel c
                            JOIN pim_catalog_channel_locale cl ON cl.channel_id = c.id
                            JOIN pim_catalog_locale l ON l.id = locale_id
                        WHERE
                            l.is_activated = 1
                    ) as locale_channel ON value_per_channel = 1 AND value_per_locale = 1
                WHERE
                    reference_entity_identifier = :reference_entity_identifier
            ) as mask;
SQL;

        $statement = $this->sqlConnection->executeQuery($query, [
            'reference_entity_identifier' => $referenceEntityIdentifier,
        ]);

        $rows = $statement->fetchAll(\PDO::FETCH_COLUMN);

        return $this->createValueKeyCollection($rows);
    }

    private function createValueKeyCollection($rows): ValueKeyCollection
    {
        $valueKeys = [];
        foreach ($rows as $row) {
            $valueKeys[] = ValueKey::createFromNormalized($row);
        }

        return ValueKeyCollection::fromValueKeys($valueKeys);
    }
}
