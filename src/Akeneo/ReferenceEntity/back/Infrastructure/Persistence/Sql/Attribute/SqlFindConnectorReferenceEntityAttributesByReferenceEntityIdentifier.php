<?php

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\ConnectorAttribute;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\FindConnectorReferenceEntityAttributesByReferenceEntityIdentifierInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator\AttributeHydratorRegistry;
use Doctrine\DBAL\Connection;

class SqlFindConnectorReferenceEntityAttributesByReferenceEntityIdentifier implements FindConnectorReferenceEntityAttributesByReferenceEntityIdentifierInterface
{
    /** @var Connection */
    private $sqlConnection;

    /** @var AttributeHydratorRegistry */
    private $attributeHydratorRegistry;

    /**
     * @param Connection $sqlConnection
     */
    public function __construct(Connection $sqlConnection, AttributeHydratorRegistry $attributeHydratorRegistry)
    {
        $this->sqlConnection = $sqlConnection;
        $this->attributeHydratorRegistry = $attributeHydratorRegistry;
    }

    /**
     * @return ConnectorAttribute[]
     */
    public function __invoke(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        $results = $this->fetchResult($referenceEntityIdentifier);

        return $this->hydrateAttributes($results);
    }

    private function fetchResult(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        $query = <<<SQL
        SELECT
            identifier,
            code,
            labels,
            reference_entity_identifier,
            attribute_order,
            is_required,
            attribute_type,
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
     * @return ConnectorAttribute[]
     */
    private function hydrateAttributes(array $results): array
    {
        $allAttributeDetails = [];
        
        foreach ($results as $result) {
            $hydratedAttribute = $this->attributeHydratorRegistry->getHydrator($result)->hydrate($result);

            $connectorAttribute = new ConnectorAttribute(
                $hydratedAttribute->getIdentifier(),
                LabelCollection::fromArray(json_decorde($result['labels'], true)),
                $result['attribute_type'],
                $hydratedAttribute->hasValuePerLocale(),
                $hydratedAttribute->hasValuePerChannel(),
                (bool) $result['is_required'],
                $this->getAdditionalProperties($hydratedAttribute->normalize())
            );

            $allAttributeDetails[] = $connectorAttribute;
        }

        return $allAttributeDetails;
    }

    private function getAdditionalProperties(array $normalizedAttribute): array
    {
        unset($normalizedAttribute['identifier']);
        unset($normalizedAttribute['reference_entity_identifier']);
        unset($normalizedAttribute['code']);
        unset($normalizedAttribute['labels']);
        unset($normalizedAttribute['order']);
        unset($normalizedAttribute['is_required']);
        unset($normalizedAttribute['value_per_channel']);
        unset($normalizedAttribute['value_per_locale']);
        unset($normalizedAttribute['type']);

        return $normalizedAttribute;
    }
}
