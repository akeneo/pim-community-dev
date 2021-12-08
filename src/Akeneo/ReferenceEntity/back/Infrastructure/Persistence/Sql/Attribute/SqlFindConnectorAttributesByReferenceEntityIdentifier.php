<?php

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\ConnectorAttribute;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\FindConnectorAttributesByReferenceEntityIdentifierInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator\AttributeHydratorRegistry;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\InactiveLabelFilter;
use Doctrine\DBAL\Connection;

class SqlFindConnectorAttributesByReferenceEntityIdentifier implements FindConnectorAttributesByReferenceEntityIdentifierInterface
{
    public function __construct(private Connection $sqlConnection, private AttributeHydratorRegistry $attributeHydratorRegistry, private InactiveLabelFilter $inactiveLabelFilter)
    {
    }

    /**
     * @return ConnectorAttribute[]
     */
    public function find(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        $results = $this->fetchAll($referenceEntityIdentifier);

        return $this->hydrateAttributes($results);
    }

    private function fetchAll(ReferenceEntityIdentifier $referenceEntityIdentifier): array
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
        WHERE reference_entity_identifier = :reference_entity_identifier
        ORDER BY attribute_order ASC;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $query,
            ['reference_entity_identifier' => $referenceEntityIdentifier->normalize()]
        );
        $result = $statement->fetchAllAssociative();

        return $result ?: [];
    }

    /**
     * @return ConnectorAttribute[]
     */
    private function hydrateAttributes(array $results): array
    {
        $allAttributeDetails = [];

        foreach ($results as $result) {
            $hydratedAttribute = $this->attributeHydratorRegistry->getHydrator($result)->hydrate($result);
            $labels = json_decode($result['labels'], true);
            $labels = $this->inactiveLabelFilter->filter($labels);

            $connectorAttribute = new ConnectorAttribute(
                $hydratedAttribute->getCode(),
                LabelCollection::fromArray($labels),
                $result['attribute_type'],
                AttributeValuePerLocale::fromBoolean($hydratedAttribute->hasValuePerLocale()),
                AttributeValuePerChannel::fromBoolean($hydratedAttribute->hasValuePerChannel()),
                AttributeIsRequired::fromBoolean((bool) $result['is_required']),
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
        unset($normalizedAttribute['options']);

        return $normalizedAttribute;
    }
}
