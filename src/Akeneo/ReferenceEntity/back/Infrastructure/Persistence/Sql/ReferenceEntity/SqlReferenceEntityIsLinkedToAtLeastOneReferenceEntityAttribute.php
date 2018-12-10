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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityIsLinkedToAtLeastOneReferenceEntityAttributeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SqlReferenceEntityIsLinkedToAtLeastOneReferenceEntityAttribute implements ReferenceEntityIsLinkedToAtLeastOneReferenceEntityAttributeInterface
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function __invoke(ReferenceEntityIdentifier $identifier): bool
    {
        return $this->isReferenceEntityLinkedToAtLeastOneReferenceEntityAttribute($identifier);
    }

    private function isReferenceEntityLinkedToAtLeastOneReferenceEntityAttribute(ReferenceEntityIdentifier $identifier): bool
    {
        $query = <<<SQL
        SELECT EXISTS (
            SELECT 1
            FROM akeneo_reference_entity_attribute
            WHERE (
                attribute_type = :recordAttributeType OR
                attribute_type = :recordCollectionAttributeType
            )
            AND JSON_CONTAINS(additional_properties, :jsonRecordType)
        ) as is_linked
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'recordAttributeType' => 'record',
            'recordCollectionAttributeType' => 'record_collection',
            'jsonRecordType' => sprintf('{"record_type": "%s"}', $identifier),
        ]);

        $platform = $this->sqlConnection->getDatabasePlatform();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $isLinked = Type::getType(Type::BOOLEAN)->convertToPhpValue($result['is_linked'], $platform);

        return $isLinked;
    }
}
