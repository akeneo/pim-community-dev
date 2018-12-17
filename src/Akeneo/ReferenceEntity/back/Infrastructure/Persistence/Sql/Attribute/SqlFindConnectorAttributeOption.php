<?php

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\ConnectorAttribute;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\ConnectorAttributeOption;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\FindConnectorAttributeByIdentifierAndCodeInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\FindConnectorAttributeOptionInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator\AttributeHydratorRegistry;
use Doctrine\DBAL\Connection;
use PhpParser\Node\Stmt\Label;

class  SqlFindConnectorAttributeOption implements FindConnectorAttributeOptionInterface
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
    public function __invoke(ReferenceEntityIdentifier $referenceEntityIdentifier, AttributeCode $attributeCode, OptionCode $optionCode): ?ConnectorAttributeOption
    {
        return $this->fetch($referenceEntityIdentifier, $attributeCode, $optionCode);
    }

    private function fetch(ReferenceEntityIdentifier $referenceEntityIdentifier, AttributeCode $attributeCode, OptionCode $optionCode): ?ConnectorAttributeOption
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
        // @TODO - maybe check the attribute type too
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

        $connectorOption = new ConnectorAttributeOption(
            OptionCode::fromString($matchingOption['code']),
            LabelCollection::fromArray($matchingOption['labels'])
        );

        return $connectorOption;
    }
}
