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

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\ConnectorAttribute;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\ConnectorAttributeOption;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\FindConnectorAttributeOptionsInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute\Hydrator\AttributeHydratorRegistry;
use Doctrine\DBAL\Connection;

class SqlFindConnectorAttributeOptions implements FindConnectorAttributeOptionsInterface
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
     * @return ConnectorAttribute
     */
    public function find(AssetFamilyIdentifier $assetFamilyIdentifier, AttributeCode $attributeCode): array
    {
        return $this->fetch($assetFamilyIdentifier, $attributeCode);
    }

    private function fetch(AssetFamilyIdentifier $assetFamilyIdentifier, AttributeCode $attributeCode): array
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
                'attribute_code' => (string) $attributeCode
            ]
        );

        $result = $statement->fetch();

        if (!$result) {
            return null;
        }

        return $this->hydrateAttributeOptions($result);
    }

    /**
     * @return ConnectorAttribute
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
            $connectorOptions[] = new ConnectorAttributeOption(
                OptionCode::fromString($option['code']),
                LabelCollection::fromArray($option['labels'])
            );
        }

        return $connectorOptions;
    }
}
