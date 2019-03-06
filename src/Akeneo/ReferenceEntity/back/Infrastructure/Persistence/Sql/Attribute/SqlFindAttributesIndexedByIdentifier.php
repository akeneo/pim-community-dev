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

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator\AttributeHydratorRegistry;
use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindAttributesIndexedByIdentifier implements FindAttributesIndexedByIdentifierInterface
{
    /** @var Connection */
    private $sqlConnection;

    /** @var AttributeHydratorRegistry */
    private $attributeHydratorRegistry;

    /** @var array */
    private $cachedResult = [];

    public function __construct(Connection $sqlConnection, AttributeHydratorRegistry $attributeHydratorRegistry)
    {
        $this->sqlConnection = $sqlConnection;
        $this->attributeHydratorRegistry = $attributeHydratorRegistry;
    }

    /**
     * List of attributes indexed by their identifier
     *
     * @return AbstractAttribute[]
     */
    public function __invoke(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        if (!isset($this->cachedResult[$referenceEntityIdentifier->normalize()])) {
            $results = $this->fetchResult($referenceEntityIdentifier);
            $this->cachedResult[$referenceEntityIdentifier->normalize()] = $this->hydrateAttributes($results);
        }

        return $this->cachedResult[$referenceEntityIdentifier->normalize()];
    }

    private function fetchResult(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        $query = <<<SQL
        SELECT
            identifier,
            code,
            reference_entity_identifier,
            labels,
            attribute_type,
            attribute_order,
            is_required,
            value_per_channel,
            value_per_locale,
            additional_properties
        FROM akeneo_reference_entity_attribute
        WHERE reference_entity_identifier = :reference_entity_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $query,
            ['reference_entity_identifier' => (string) $referenceEntityIdentifier]
        );
        $result = $statement->fetchAll();

        return !$result ? [] : $result;
    }

    /**
     * List of attributes indexed by their identifier
     *
     * @return AbstractAttribute[]
     */
    private function hydrateAttributes(array $results): array
    {
        $indexedAttributes = [];
        foreach ($results as $result) {
            $attribute = $this->attributeHydratorRegistry->getHydrator($result)->hydrate($result);
            $indexedAttributes[$attribute->getIdentifier()->normalize()] = $attribute;
        }

        return $indexedAttributes;
    }
}
