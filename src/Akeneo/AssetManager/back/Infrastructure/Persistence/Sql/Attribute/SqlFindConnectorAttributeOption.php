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
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\FindConnectorAttributeOptionInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute\Hydrator\AttributeHydratorRegistry;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\InactiveLabelFilter;
use Doctrine\DBAL\Connection;

class SqlFindConnectorAttributeOption implements FindConnectorAttributeOptionInterface
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
        AttributeCode $attributeCode,
        OptionCode $optionCode
    ): ?ConnectorAttributeOption {
        return $this->fetch($assetFamilyIdentifier, $attributeCode, $optionCode);
    }

    private function fetch(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeCode $attributeCode,
        OptionCode $optionCode
    ): ?ConnectorAttributeOption {
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
            return null;
        }

        return $this->hydrateAttributeOption($result, (string) $optionCode);
    }

    private function hydrateAttributeOption(array $result, string $optionCode): ?ConnectorAttributeOption
    {
        $additionalProperties = json_decode($result['additional_properties'], true);

        $options = $additionalProperties['options'];

        if (null === $options || empty($options)) {
            return null;
        }

        $matchingOption = current(array_filter($options, fn($option) => $option['code'] === $optionCode));

        if (!$matchingOption) {
            return null;
        }

        $labels = $matchingOption['labels'];
        $labels = $this->inactiveLabelFilter->filter($labels);

        return new ConnectorAttributeOption(
            OptionCode::fromString($matchingOption['code']),
            LabelCollection::fromArray($labels)
        );
    }
}
