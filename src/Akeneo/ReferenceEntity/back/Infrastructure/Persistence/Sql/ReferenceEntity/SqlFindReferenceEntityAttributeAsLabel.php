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
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function __invoke(ReferenceEntityIdentifier $referenceEntityIdentifier): AttributeAsLabelReference
    {
        $query = <<<SQL
        SELECT attribute_as_label
        FROM akeneo_reference_entity_reference_entity
        WHERE identifier = :identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'identifier' => (string) $referenceEntityIdentifier,
        ]);

        $attributeAsLabel = $statement->fetchColumn();
        $statement->closeCursor();

        return false === $attributeAsLabel ?
            AttributeAsLabelReference::noReference() :
            AttributeAsLabelReference::createFromNormalized($attributeAsLabel);
    }
}
