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
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\FindConnectorAttributeOptionInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator\AttributeHydratorRegistry;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\InactiveLabelFilter;
use Doctrine\DBAL\Connection;

class SqlFindConnectorAttributeOption implements FindConnectorAttributeOptionInterface
{
    public function __construct(
        private Connection $sqlConnection,
        private InactiveLabelFilter $inactiveLabelFilter
    ) {
    }

    public function find(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $attributeCode,
        OptionCode $optionCode
    ): ?ConnectorAttributeOption {
        return $this->fetch($referenceEntityIdentifier, $attributeCode, $optionCode);
    }

    private function fetch(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $attributeCode,
        OptionCode $optionCode
    ): ?ConnectorAttributeOption {
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

        $result = $statement->fetchAssociative();

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

        $matchingOption = current(array_filter($options, static fn ($option) => $option['code'] === $optionCode));

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
