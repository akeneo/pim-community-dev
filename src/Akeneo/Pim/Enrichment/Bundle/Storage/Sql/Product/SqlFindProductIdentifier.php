<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Component\Product\Query\FindIdentifier;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlFindProductIdentifier implements FindIdentifier
{
    public function __construct(private Connection $connection)
    {
    }

    public function fromUuid(string $uuid): null|string
    {
        $identifier = $this->connection->executeQuery(<<<SQL
SELECT raw_data AS identifier
FROM pim_catalog_product_unique_data pcpud
INNER JOIN pim_catalog_attribute a ON pcpud.attribute_id = a.id 
WHERE product_uuid = :uuid
AND main_identifier = 1
SQL,
            ['uuid' => Uuid::fromString($uuid)->getBytes()]
        )->fetchOne();

        return false === $identifier ? null : $identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function fromUuids(array $uuids): array
    {
        if ([] === $uuids) {
            return [];
        }
        Assert::allString($uuids);

        $uuidsAsBytes = \array_map(function (string $uuid) {
            if (!Uuid::isValid($uuid)) {
                throw new \InvalidArgumentException(sprintf('Uuid should be a valid uuid, %s given', $uuid));
            }
            return Uuid::fromString($uuid)->getBytes();
        }, $uuids);

        $stmt = $this->connection->executeQuery(<<<SQL
WITH main_identifier AS (
    SELECT id
    FROM pim_catalog_attribute
    WHERE main_identifier = 1
    LIMIT 1
)
SELECT BIN_TO_UUID(uuid) AS uuid, raw_data AS identifier 
FROM pim_catalog_product
LEFT JOIN pim_catalog_product_unique_data pcpud
    ON pcpud.product_uuid = pim_catalog_product.uuid
    AND pcpud.attribute_id = (SELECT id FROM main_identifier)
WHERE uuid IN (:uuids)
SQL,
            ['uuids' => $uuidsAsBytes],
            ['uuids' => Connection::PARAM_STR_ARRAY]
        );

        $identifiers = [];
        while ($row = $stmt->fetchAssociative()) {
            $identifiers[$row['uuid']] = $row['identifier'];
        }

        return $identifiers;
    }
}
