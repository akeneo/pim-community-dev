<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\ConnectorAttributeOption;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\FindConnectorAttributeOptionsInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute\Hydrator\AttributeHydratorRegistry;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\InactiveLabelFilter;
use Doctrine\DBAL\Connection;

class SqlFindConnectorAttributeOptions implements FindConnectorAttributeOptionsInterface
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

    /**
     * @return ConnectorAttributeOption[]
     */
    public function find(AssetFamilyIdentifier $assetFamilyIdentifier, AttributeCode $attributeCode): array
    {
        return $this->fetch($assetFamilyIdentifier, $attributeCode);
    }

    /**
     * @return ConnectorAttributeOption[]
     */
    private function fetch(AssetFamilyIdentifier $assetFamilyIdentifier, AttributeCode $attributeCode): ?array
    {
        $query = <<<SQL
        SELECT additional_properties
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
            return [];
        }

        return $this->hydrateAttributeOptions($result);
    }

    /**
     * @return ConnectorAttributeOption[]
     */
    private function hydrateAttributeOptions(array $result): array
    {
        $additionalProperties = json_decode($result['additional_properties'], true) ?? [];
        $options = $additionalProperties['options'] ?? null;

        if (null === $options) {
            throw new \LogicException('Attribute %s has no options');
        }

        $connectorOptions = [];

        foreach ($options as $option) {
            $labels = $option['labels'];
            $labels = $this->inactiveLabelFilter->filter($labels);

            $connectorOptions[] = new ConnectorAttributeOption(
                OptionCode::fromString($option['code']),
                LabelCollection::fromArray($labels)
            );
        }

        return $connectorOptions;
    }
}
