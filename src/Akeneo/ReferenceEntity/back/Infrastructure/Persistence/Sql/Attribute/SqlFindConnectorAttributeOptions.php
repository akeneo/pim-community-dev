<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\ConnectorAttributeOption;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\FindConnectorAttributeOptionsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator\AttributeHydratorRegistry;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\InactiveLabelFilter;
use Doctrine\DBAL\Connection;

class SqlFindConnectorAttributeOptions implements FindConnectorAttributeOptionsInterface
{
    /** @var Connection */
    private $sqlConnection;

    /** @var AttributeHydratorRegistry */
    private $attributeHydratorRegistry;

    /** @var InactiveLabelFilter */
    private $inactiveLabelFilter;

    public function __construct(
        Connection $sqlConnection,
        AttributeHydratorRegistry $attributeHydratorRegistry,
        InactiveLabelFilter $inactiveLabelFilter
    ) {
        $this->sqlConnection = $sqlConnection;
        $this->attributeHydratorRegistry = $attributeHydratorRegistry;
        $this->inactiveLabelFilter = $inactiveLabelFilter;
    }

    public function find(ReferenceEntityIdentifier $referenceEntityIdentifier, AttributeCode $attributeCode): array
    {
        return $this->fetch($referenceEntityIdentifier, $attributeCode);
    }

    private function fetch(ReferenceEntityIdentifier $referenceEntityIdentifier, AttributeCode $attributeCode): array
    {
        $query = <<<SQL
        SELECT additional_properties
        FROM akeneo_reference_entity_attribute
        WHERE reference_entity_identifier = :reference_entity_identifier
        AND code = :attribute_code
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $query,
            [
                'reference_entity_identifier' => $referenceEntityIdentifier->normalize(),
                'attribute_code' => (string) $attributeCode,
            ]
        );

        $result = $statement->fetch();

        if (!$result) {
            return [];
        }

        return $this->hydrateAttributeOptions($result);
    }

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
