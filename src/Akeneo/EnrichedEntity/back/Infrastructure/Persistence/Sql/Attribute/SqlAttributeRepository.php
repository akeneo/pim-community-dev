<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeNotFoundException;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator\AttributeHydratorRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Types\Type;
use PDO;
use Ramsey\Uuid\Uuid;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlAttributeRepository implements AttributeRepositoryInterface
{
    /** @var Connection */
    private $sqlConnection;

    /** @var AttributeHydratorRegistry */
    private $attributeHydratorRegistry;

    public function __construct(Connection $sqlConnection, AttributeHydratorRegistry $attributeHydratorRegistry)
    {
        $this->sqlConnection = $sqlConnection;
        $this->attributeHydratorRegistry = $attributeHydratorRegistry;
    }

    public function create(AbstractAttribute $attribute): void
    {
        $normalizedAttribute = $attribute->normalize();
        $additionalProperties = $this->getAdditionalProperties($normalizedAttribute);
        $insert = <<<SQL
        INSERT INTO akeneo_enriched_entity_attribute (
            identifier,
            code,
            enriched_entity_identifier,
            labels,
            attribute_type,
            attribute_order,
            is_required,
            value_per_channel,
            value_per_locale,
            additional_properties
        )
        VALUES (
            :identifier,
            :code,
            :enriched_entity_identifier,
            :labels,
            :attribute_type,
            :attribute_order,
            :is_required,
            :value_per_channel,
            :value_per_locale,
            :additional_properties
        );
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $insert,
            [
                'identifier'                 => $normalizedAttribute['identifier'],
                'code'                       => $normalizedAttribute['code'],
                'enriched_entity_identifier' => $normalizedAttribute['enriched_entity_identifier'],
                'labels'                     => json_encode($normalizedAttribute['labels']),
                'attribute_type'             => $normalizedAttribute['type'],
                'attribute_order'            => $normalizedAttribute['order'],
                'is_required'                => $normalizedAttribute['is_required'],
                'value_per_channel'          => $normalizedAttribute['value_per_channel'],
                'value_per_locale'           => $normalizedAttribute['value_per_locale'],
                'additional_properties'      => json_encode($additionalProperties),
            ],
            [
                'is_required'       => Type::getType('boolean'),
                'value_per_channel' => Type::getType('boolean'),
                'value_per_locale'  => Type::getType('boolean'),
            ]
        );
        if ($affectedRows > 1) {
            throw new \RuntimeException(
                sprintf('Expected to create one attribute, but %d rows were affected', $affectedRows)
            );
        }
    }

    public function update(AbstractAttribute $attribute): void
    {
        $normalizedAttribute = $attribute->normalize();
        $additionalProperties = $this->getAdditionalProperties($normalizedAttribute);
        $update = <<<SQL
        UPDATE akeneo_enriched_entity_attribute SET
            labels = :labels,
            attribute_order = :attribute_order,
            is_required = :is_required,
            additional_properties = :additional_properties
        WHERE identifier = :identifier AND enriched_entity_identifier = :enriched_entity_identifier;
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $update,
            [
                'identifier'                 => $normalizedAttribute['code'],
                'enriched_entity_identifier' => $normalizedAttribute['enriched_entity_identifier'],
                'labels'                     => $normalizedAttribute['labels'],
                'attribute_order'            => $normalizedAttribute['order'],
                'is_required'                => $normalizedAttribute['is_required'],
                'additional_properties'      => json_encode($additionalProperties),
            ],
            [
                'is_required' => Type::getType('boolean'),
                'labels' => Type::getType('json_array')
            ]
        );
        if ($affectedRows > 1) {
            throw new \RuntimeException(
                sprintf('Expected to edit one attribute, but %d rows were affected', $affectedRows)
            );
        }
    }

    /**
     * @throws AttributeNotFoundException
     * @throws DBALException
     */
    public function getByIdentifier(AttributeIdentifier $identifier): AbstractAttribute
    {
        $fetch = <<<SQL
        SELECT
            identifier,
            code,
            enriched_entity_identifier,
            labels,
            attribute_type,
            attribute_order,
            is_required,
            value_per_channel,
            value_per_locale,
            additional_properties
        FROM akeneo_enriched_entity_attribute
        WHERE identifier = :identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            [
                'identifier' => $identifier,
            ]
        );
        $result = $statement->fetch();

        if (!$result) {
            throw AttributeNotFoundException::withIdentifier($identifier);
        }

        return $this->attributeHydratorRegistry->getHydrator($result)->hydrate($this->sqlConnection->getDatabasePlatform(),
            $result);
    }

    /**
     * @param EnrichedEntityIdentifier $enrichedEntityIdentifier
     *
     * @return AbstractAttribute[]
     * @throws DBALException
     */
    public function findByEnrichedEntity(EnrichedEntityIdentifier $enrichedEntityIdentifier): array
    {
        $fetch = <<<SQL
        SELECT
            identifier,
            enriched_entity_identifier,
            labels,
            attribute_type,
            attribute_order,
            is_required,
            value_per_channel,
            value_per_locale,
            additional_properties
        FROM akeneo_enriched_entity_attribute
        WHERE enriched_entity_identifier = :enriched_entity_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            [
                'enriched_entity_identifier' => $enrichedEntityIdentifier,
            ]
        );
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        $attributes = [];
        foreach ($results as $result) {
            $attributes[] = $this->attributeHydratorRegistry
                ->getHydrator($result)
                ->hydrate($this->sqlConnection->getDatabasePlatform(), $result);
        }

        return $attributes;
    }

    private function getAdditionalProperties(array $normalizedAttribute): array
    {
        unset($normalizedAttribute['identifier']);
        unset($normalizedAttribute['enriched_entity_identifier']);
        unset($normalizedAttribute['code']);
        unset($normalizedAttribute['labels']);
        unset($normalizedAttribute['order']);
        unset($normalizedAttribute['is_required']);
        unset($normalizedAttribute['value_per_channel']);
        unset($normalizedAttribute['value_per_locale']);
        unset($normalizedAttribute['type']);

        return $normalizedAttribute;
    }

    /**
     * @throws AttributeNotFoundException
     * @throws DBALException
     */
    public function deleteByIdentifier(AttributeIdentifier $identifier): void
    {
        $sql = <<<SQL
        DELETE FROM akeneo_enriched_entity_attribute
        WHERE identifier = :identifier;
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $sql,
            [
                'identifier' => $identifier,
            ]
        );
        if (1 !== $affectedRows) {
            throw AttributeNotFoundException::withIdentifier($identifier);
        }
    }

    public function nextIdentifier(
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        AttributeCode $attributeCode
    ): AttributeIdentifier {
        return AttributeIdentifier::create(
            (string) $enrichedEntityIdentifier,
            (string) $attributeCode,
            Uuid::uuid4()->toString()
        );
    }
}
