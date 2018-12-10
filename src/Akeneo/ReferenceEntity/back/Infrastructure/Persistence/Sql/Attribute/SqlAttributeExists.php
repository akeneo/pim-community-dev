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

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeExistsInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlAttributeExists implements AttributeExistsInterface
{
    /** @var Connection */
    private $sqlConnection;

    /**
     * @param Connection $sqlConnection
     */
    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function withIdentifier(AttributeIdentifier $identifier): bool
    {
        $query = <<<SQL
        SELECT EXISTS (
            SELECT 1
            FROM akeneo_reference_entity_attribute
            WHERE identifier = :identifier
        ) as is_existing
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'identifier' => $identifier,
        ]);

        $platform = $this->sqlConnection->getDatabasePlatform();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $statement->closeCursor();
        $isExisting = Type::getType(Type::BOOLEAN)->convertToPhpValue($result['is_existing'], $platform);

        return $isExisting;
    }

    public function withReferenceEntityAndCode(ReferenceEntityIdentifier $referenceEntityIdentifier, AttributeCode $attributeCode): bool
    {
        $query = <<<SQL
        SELECT EXISTS (
            SELECT 1
            FROM akeneo_reference_entity_attribute
            WHERE code = :code AND reference_entity_identifier = :reference_entity_identifier
        ) as is_existing
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'code' => $attributeCode,
            'reference_entity_identifier' => $referenceEntityIdentifier,
        ]);

        $platform = $this->sqlConnection->getDatabasePlatform();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $statement->closeCursor();
        $isExisting = Type::getType(Type::BOOLEAN)->convertToPhpValue($result['is_existing'], $platform);

        return $isExisting;
    }

    public function withReferenceEntityIdentifierAndOrder(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeOrder $order
    ): bool {
        $query = <<<SQL
        SELECT EXISTS (
            SELECT 1
            FROM akeneo_reference_entity_attribute
            WHERE attribute_order = :attribute_order AND reference_entity_identifier = :reference_entity_identifier
        ) as is_existing
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'attribute_order' => $order->intValue(),
            'reference_entity_identifier' => (string) $referenceEntityIdentifier,
        ]);

        $platform = $this->sqlConnection->getDatabasePlatform();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $statement->closeCursor();
        $isExisting = Type::getType(Type::BOOLEAN)->convertToPhpValue($result['is_existing'], $platform);

        return $isExisting;
    }
}
