<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKey;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use Doctrine\DBAL\Driver\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindValueKeysToFilterOn
{
    /** @var Connection */
    private $sqlConnection;

    /** @var array */
    private $cachedResult;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function fetch(string $referenceEntityIdentifier): array
    {
        if (!isset($this->cachedResult[$referenceEntityIdentifier])) {
            $this->cachedResult[$referenceEntityIdentifier] = $this->fetchValueKeys($referenceEntityIdentifier);
        }

        return $this->cachedResult[$referenceEntityIdentifier];
    }

    private function fetchValueKeys(string $referenceEntityIdentifier): array
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
                    a.reference_entity_identifier = :reference_entity_identifier
                    AND a.attribute_type IN ('option', 'option_collection', 'record', 'record_collection')
                ) as mask;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, ['reference_entity_identifier' => $referenceEntityIdentifier]);

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

}
