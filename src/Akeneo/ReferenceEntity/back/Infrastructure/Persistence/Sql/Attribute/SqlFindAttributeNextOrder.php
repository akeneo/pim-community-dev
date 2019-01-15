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

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributeNextOrderInterface;
use Doctrine\DBAL\Connection;

class SqlFindAttributeNextOrder implements FindAttributeNextOrderInterface
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

    public function withReferenceEntityIdentifier(ReferenceEntityIdentifier $referenceEntityIdentifier): AttributeOrder
    {
        $query = <<<SQL
        SELECT MAX(attribute_order)
        FROM akeneo_reference_entity_attribute
        WHERE reference_entity_identifier = :reference_entity_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'reference_entity_identifier' => $referenceEntityIdentifier,
        ]);
        $result = $statement->fetchColumn();
        $statement->closeCursor();

        return null === $result ? AttributeOrder::fromInteger(0) : AttributeOrder::fromInteger((intval($result) + 1));
    }
}
