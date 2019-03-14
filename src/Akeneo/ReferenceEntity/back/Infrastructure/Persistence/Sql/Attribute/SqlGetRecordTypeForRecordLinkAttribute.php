<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException;
use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class SqlGetRecordTypeForRecordLinkAttribute
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function fetch(string $attributeIdentifier): string
    {
        $sql = <<<SQL
    SELECT additional_properties
    FROM akeneo_reference_entity_attribute 
    WHERE attribute_type IN ('record', 'record_collection') AND identifier = :attribute_identifier;
SQL;

        $stmt = $this->sqlConnection->executeQuery($sql, ['attribute_identifier' => $attributeIdentifier]);
        $result = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        if (empty($result)) {
            throw new InvalidArgumentException(
                sprintf('No reference entity type found for attribute "%s"', $attributeIdentifier)
            );
        }
        $additonalProperties = json_decode(current($result), true);
        if (null !== $additonalProperties && !isset($additonalProperties['record_type'])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected to have the attribute property "record_type" for attribute "%s"',
                    $attributeIdentifier
                )
            );
        }

        return $additonalProperties['record_type'];
    }
}
