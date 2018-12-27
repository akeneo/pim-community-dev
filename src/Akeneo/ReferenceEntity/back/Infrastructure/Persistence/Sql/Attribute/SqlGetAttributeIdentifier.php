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
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

class SqlGetAttributeIdentifier implements GetAttributeIdentifierInterface
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function withReferenceEntityAndCode(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $attributeCode
    ): AttributeIdentifier
    {
        $query = <<<SQL
            SELECT identifier
            FROM akeneo_reference_entity_attribute
            WHERE code = :code AND reference_entity_identifier = :reference_entity_identifier
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'code' => $attributeCode,
            'reference_entity_identifier' => $referenceEntityIdentifier,
        ]);
        $platform = $this->sqlConnection->getDatabasePlatform();
        $result = $statement->fetch();

        if (!isset($result['identifier'])) {
            throw new \LogicException(
                sprintf(
                    'Attribute identifier not found for "%s" attribute code and "%s" reference entity identifier.',
                    $attributeCode,
                    $referenceEntityIdentifier
                )
            );
        }

        $identifier = Type::getType(Type::TEXT)->convertToPhpValue($result['identifier'], $platform);

        return AttributeIdentifier::fromString($identifier);
    }
}
