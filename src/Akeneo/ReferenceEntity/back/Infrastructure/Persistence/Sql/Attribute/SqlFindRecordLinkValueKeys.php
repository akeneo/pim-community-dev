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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Doctrine\DBAL\Connection;
use PDO;

/**
 * Find record link value keys for every attribute of type "record" and "record_collection" of the given Reference Entity.
 * It also returns metadata of the attribute itself such as the "record type" of the attribute.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class SqlFindRecordLinkValueKeys
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    /**
     * Example:
     * [
     *     [
     *          'value_key' => 'cities_designer_5ea58434-4f15-4a38-9d67-c8ad06842693_de_DE',
     *          'attribute_identifier' => 'cities_designer_5ea58434-4f15-4a38-9d67-c8ad06842693',
     *          'record_type' => 'city',
     *          'attribute_type' => 'record_collection',
     *     ],
     *     [
     *          'value_key' => 'maincity_designer_30b27d3b-65e5-41ef-86ab-d6ecdbc4436a',
     *          'attribute_identifier' => 'maincity_designer_30b27d3b-65e5-41ef-86ab-d6ecdbc4436a',
     *          'record_type' => 'city',
     *          'attribute_type' => 'record',
     *     ]
     * ]
     */
    public function fetch(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        $query = <<<SQL
        SELECT
          CONCAT(
              mask.identifier,
              IF(mask.value_per_channel, CONCAT('_', mask.channel_code), ''),
              IF(mask.value_per_locale, CONCAT('_', mask.locale_code), '')
            ) as `value_key`,
          mask.identifier as `attribute_identifier`,
          mask.record_type,
          mask.attribute_type
        FROM (
               SELECT
                 a.identifier,
                 a.attribute_type,
                 JSON_UNQUOTE(JSON_EXTRACT(a.additional_properties, '$.record_type')) as record_type,
                 a.value_per_channel,
                 a.value_per_locale,
                 COALESCE(c.code, locale_channel.channel_code) as channel_code,
                 COALESCE(l.code, locale_channel.locale_code) as locale_code
               FROM
                   (SELECT * FROM akeneo_reference_entity_attribute WHERE attribute_type IN ('record', 'record_collection')) as a
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

        $statement = $this->sqlConnection->executeQuery(
            $query,
            [
                'reference_entity_identifier' => (string) $referenceEntityIdentifier,
            ]
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
