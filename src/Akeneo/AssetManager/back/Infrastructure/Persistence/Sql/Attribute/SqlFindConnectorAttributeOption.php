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
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\ConnectorAttribute;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\ConnectorAttributeOption;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\FindConnectorAttributeOptionInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute\Hydrator\AttributeHydratorRegistry;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\InactiveLabelFilter;
use Doctrine\DBAL\Connection;

class SqlFindConnectorAttributeOption implements FindConnectorAttributeOptionInterface
{
    /** @var Connection */
    private $sqlConnection;

    /** @var AttributeHydratorRegistry */
    private $attributeHydratorRegistry;

    /** @var InactiveLabelFilter */
    private $inactiveLabelFilter;

    // @todo merge master: make $inactiveLabelFilter mandatory
    public function __construct(
        Connection $sqlConnection,
        AttributeHydratorRegistry $attributeHydratorRegistry,
        InactiveLabelFilter $inactiveLabelFilter = null
    ) {
        $this->sqlConnection = $sqlConnection;
        $this->attributeHydratorRegistry = $attributeHydratorRegistry;
        $this->inactiveLabelFilter = $inactiveLabelFilter;
    }

    /**
     * @return ConnectorAttribute
     */
    public function find(AssetFamilyIdentifier $assetFamilyIdentifier, AttributeCode $attributeCode, OptionCode $optionCode): ?ConnectorAttributeOption
    {
        return $this->fetch($assetFamilyIdentifier, $attributeCode, $optionCode);
    }

    private function fetch(AssetFamilyIdentifier $assetFamilyIdentifier, AttributeCode $attributeCode, OptionCode $optionCode): ?ConnectorAttributeOption
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

        return $this->hydrateAttributeOption($result, (string) $optionCode);
    }

    /**
     * @return ConnectorAttribute
     */
    private function hydrateAttributeOption(array $result, string $optionCode): ?ConnectorAttributeOption
    {
        $additionalProperties = json_decode($result['additional_properties'], true);

        $options = $additionalProperties['options'];

        if (null === $options || empty($options)) {
            return null;
        }

        $matchingOption = current(array_filter($options, function ($option) use ($optionCode) {
            return $option['code'] === $optionCode;
        }));


        if (!$matchingOption) {
            return null;
        }

        $labels = $matchingOption['labels'];
        // @todo merge master: remove null check
        if ($this->inactiveLabelFilter !== null) {
            $labels = $this->inactiveLabelFilter->filter($labels);
        }

        $connectorOption = new ConnectorAttributeOption(
            OptionCode::fromString($matchingOption['code']),
            LabelCollection::fromArray($labels)
        );

        return $connectorOption;
    }
}
