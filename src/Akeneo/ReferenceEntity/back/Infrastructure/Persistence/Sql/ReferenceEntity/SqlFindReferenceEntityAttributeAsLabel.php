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

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsLabelReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\FindReferenceEntityAttributeAsLabelInterface;
use Doctrine\DBAL\Connection;

class SqlFindReferenceEntityAttributeAsLabel implements FindReferenceEntityAttributeAsLabelInterface
{
    public function __construct(
        private Connection $sqlConnection
    ) {
    }

    public function find(ReferenceEntityIdentifier $referenceEntityIdentifier): AttributeAsLabelReference
    {
        $query = <<<SQL
        SELECT attribute_as_label
        FROM akeneo_reference_entity_reference_entity
        WHERE identifier = :identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'identifier' => (string) $referenceEntityIdentifier,
        ]);

        $attributeAsLabel = $statement->fetchOne();
        $statement->free();

        return false === $attributeAsLabel ?
            AttributeAsLabelReference::noReference() :
            AttributeAsLabelReference::createFromNormalized($attributeAsLabel);
    }
}
