<?php

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\ConnectorAttribute;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\FindConnectorAttributeByIdentifierAndCodeInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute\Hydrator\AttributeHydratorRegistry;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\InactiveLabelFilter;
use Doctrine\DBAL\Connection;

class SqlFindConnectorAttributeByIdentifierAndCode implements FindConnectorAttributeByIdentifierAndCodeInterface
{
    private Connection $sqlConnection;

    private AttributeHydratorRegistry $attributeHydratorRegistry;

    private InactiveLabelFilter $inactiveLabelFilter;

    public function __construct(
        Connection $sqlConnection,
        AttributeHydratorRegistry $attributeHydratorRegistry,
        InactiveLabelFilter $inactiveLabelFilter
    ) {
        $this->sqlConnection = $sqlConnection;
        $this->attributeHydratorRegistry = $attributeHydratorRegistry;
        $this->inactiveLabelFilter = $inactiveLabelFilter;
    }

    public function find(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeCode $attributeCode
    ): ?ConnectorAttribute {
        return $this->fetch($assetFamilyIdentifier, $attributeCode);
    }

    private function fetch(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeCode $attributeCode
    ): ?ConnectorAttribute {
        $query = <<<SQL
        SELECT
            identifier,
            code,
            labels,
            asset_family_identifier,
            attribute_order,
            is_required,
            is_read_only,
            attribute_type,
            value_per_channel,
            value_per_locale,
            additional_properties
        FROM akeneo_asset_manager_attribute
        WHERE asset_family_identifier = :asset_family_identifier
        AND code = :attribute_code
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $query,
            [
                'asset_family_identifier' => $assetFamilyIdentifier->normalize(),
                'attribute_code' => (string) $attributeCode,
            ]
        );

        $result = $statement->fetch();

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
            AttributeIsReadOnly::fromBoolean((bool) $result['is_read_only']),
            $this->getAdditionalProperties($hydratedAttribute->normalize())
        );
    }

    private function getAdditionalProperties(array $normalizedAttribute): array
    {
        unset($normalizedAttribute['identifier']);
        unset($normalizedAttribute['asset_family_identifier']);
        unset($normalizedAttribute['code']);
        unset($normalizedAttribute['labels']);
        unset($normalizedAttribute['order']);
        unset($normalizedAttribute['is_required']);
        unset($normalizedAttribute['is_read_only']);
        unset($normalizedAttribute['value_per_channel']);
        unset($normalizedAttribute['value_per_locale']);
        unset($normalizedAttribute['type']);
        unset($normalizedAttribute['options']);

        return $normalizedAttribute;
    }
}
