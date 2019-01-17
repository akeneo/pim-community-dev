<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsImageReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\FindReferenceEntityAttributeAsImageInterface;
use Doctrine\DBAL\Connection;

class SqlFindReferenceEntityAttributeAsImage implements FindReferenceEntityAttributeAsImageInterface
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function __invoke(ReferenceEntityIdentifier $referenceEntityIdentifier): AttributeAsImageReference
    {
        $query = <<<SQL
        SELECT attribute_as_image
        FROM akeneo_reference_entity_reference_entity
        WHERE identifier = :identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'identifier' => (string) $referenceEntityIdentifier,
        ]);

        $attributeAsImage = $statement->fetchColumn();
        $statement->closeCursor();

        return false === $attributeAsImage ?
            AttributeAsImageReference::noReference() :
            AttributeAsImageReference::createFromNormalized($attributeAsImage);
    }
}
