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
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindImageAttributeCodesInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindImageAttributeCodes implements FindImageAttributeCodesInterface
{
    public function __construct(
        private Connection $sqlConnection
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function find(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        $sqlQuery = <<<SQL
            SELECT code
            FROM akeneo_reference_entity_attribute
            WHERE reference_entity_identifier = :reference_entity_identifier
              AND attribute_type = :attribute_type;
SQL;

        $statement = $this->sqlConnection->executeQuery(
            $sqlQuery,
            [
                'reference_entity_identifier' => (string) $referenceEntityIdentifier,
                'attribute_type' => ImageAttribute::ATTRIBUTE_TYPE
            ]
        );

        $result = $statement->fetchAllAssociative();
        $platform = $this->sqlConnection->getDatabasePlatform();

        return array_map(static fn ($row) => AttributeCode::fromString(
            Type::getType(Types::STRING)->convertToPHPValue(
                $row['code'],
                $platform
            )
        ), $result);
    }
}
