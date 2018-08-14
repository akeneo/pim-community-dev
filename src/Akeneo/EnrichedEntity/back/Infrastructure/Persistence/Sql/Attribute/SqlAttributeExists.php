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

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\AttributeExistsInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use PDO;

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

    public function withIdentifier(AttributeIdentifier $attributeIdentifier): bool
    {
        $query = <<<SQL
        SELECT EXISTS (
            SELECT 1
            FROM akeneo_enriched_entity_attribute
            WHERE identifier = :identifier AND enriched_entity_identifier = :enriched_entity_identifier
        ) as is_existing
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'identifier' => $attributeIdentifier->getIdentifier(),
            'enriched_entity_identifier' => $attributeIdentifier->getEnrichedEntityIdentifier(),
        ]);

        $platform = $this->sqlConnection->getDatabasePlatform();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        $statement->closeCursor();
        $isExisting = Type::getType(Type::BOOLEAN)->convertToPhpValue($result['is_existing'], $platform);

        return $isExisting;
    }

    public function withEnrichedEntityIdentifierAndOrder(
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        AttributeOrder $order
    ): bool {
        $query = <<<SQL
        SELECT EXISTS (
            SELECT 1
            FROM akeneo_enriched_entity_attribute
            WHERE attribute_order = :attribute_order AND enriched_entity_identifier = :enriched_entity_identifier
        ) as is_existing
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'attribute_order' => $order->intValue(),
            'enriched_entity_identifier' => (string) $enrichedEntityIdentifier,
        ]);

        $platform = $this->sqlConnection->getDatabasePlatform();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        $statement->closeCursor();
        $isExisting = Type::getType(Type::BOOLEAN)->convertToPhpValue($result['is_existing'], $platform);

        return $isExisting;
    }
}
