<?php

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\ConnectorAttribute;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\FindConnectorAttributeByIdentifierAndCodeInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator\AttributeHydratorRegistry;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\InactiveLabelFilter;
use Doctrine\DBAL\Connection;

class SqlFindConnectorAttributeByIdentifierAndCode implements FindConnectorAttributeByIdentifierAndCodeInterface
{
    public function __construct(
        private Connection $sqlConnection,
        private AttributeHydratorRegistry $attributeHydratorRegistry,
        private InactiveLabelFilter $inactiveLabelFilter
    ) {
    }

    public function find(ReferenceEntityIdentifier $referenceEntityIdentifier, AttributeCode $attributeCode): ?ConnectorAttribute
    {
        return $this->fetch($referenceEntityIdentifier, $attributeCode);
    }

    private function fetch(ReferenceEntityIdentifier $referenceEntityIdentifier, AttributeCode $attributeCode): ?ConnectorAttribute
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
        AND code = :attribute_code
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $query,
            [
                'reference_entity_identifier' => $referenceEntityIdentifier->normalize(),
                'attribute_code' => (string) $attributeCode
            ]
        );

        $result = $statement->fetchAssociative();

        if (!$result) {
            return null;
        }

        return $this->hydrateAttribute($result);
    }

    private function hydrateAttribute(array $result): ConnectorAttribute
    {
        $hydratedAttribute = $this->attributeHydratorRegistry->getHydrator($result)->hydrate($result);

        $labels = json_decode($result['labels'], true);
        $labels = $this->inactiveLabelFilter->filter($labels);

        return new ConnectorAttribute(
            $hydratedAttribute->getCode(),
            LabelCollection::fromArray($labels),
            $result['attribute_type'],
            AttributeValuePerLocale::fromBoolean($hydratedAttribute->hasValuePerLocale()),
            AttributeValuePerChannel::fromBoolean($hydratedAttribute->hasValuePerChannel()),
            AttributeIsRequired::fromBoolean((bool) $result['is_required']),
            $this->getAdditionalProperties($hydratedAttribute->normalize())
        );
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
