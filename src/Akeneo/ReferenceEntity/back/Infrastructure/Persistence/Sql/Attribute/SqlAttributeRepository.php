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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\ReferenceEntity\Domain\Event\AttributeDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Event\BeforeAttributeDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeNotFoundException;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator\AttributeHydratorRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Types\Type;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        Connection $sqlConnection,
        AttributeHydratorRegistry $attributeHydratorRegistry,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->sqlConnection = $sqlConnection;
        $this->attributeHydratorRegistry = $attributeHydratorRegistry;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(AbstractAttribute $attribute): void
    {
        $normalizedAttribute = $attribute->normalize();
        $additionalProperties = $this->getAdditionalProperties($normalizedAttribute);
        $insert = <<<SQL
        INSERT INTO akeneo_reference_entity_attribute (
            identifier,
            code,
            reference_entity_identifier,
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
            :reference_entity_identifier,
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
                'identifier'                  => $normalizedAttribute['identifier'],
                'code'                        => $normalizedAttribute['code'],
                'reference_entity_identifier' => $normalizedAttribute['reference_entity_identifier'],
                'labels'                      => json_encode($normalizedAttribute['labels']),
                'attribute_type'              => $normalizedAttribute['type'],
                'attribute_order'             => $normalizedAttribute['order'],
                'is_required'                 => $normalizedAttribute['is_required'],
                'value_per_channel'           => $normalizedAttribute['value_per_channel'],
                'value_per_locale'            => $normalizedAttribute['value_per_locale'],
                'additional_properties'       => json_encode($additionalProperties),
            ],
            [
                'is_required'       => Type::getType(Type::BOOLEAN),
                'value_per_channel' => Type::getType(Type::BOOLEAN),
                'value_per_locale'  => Type::getType(Type::BOOLEAN),
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
        UPDATE akeneo_reference_entity_attribute SET
            labels = :labels,
            attribute_order = :attribute_order,
            is_required = :is_required,
            additional_properties = :additional_properties
        WHERE identifier = :identifier;
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $update,
            [
                'identifier'                  => $normalizedAttribute['identifier'],
                'reference_entity_identifier' => $normalizedAttribute['reference_entity_identifier'],
                'labels'                      => $normalizedAttribute['labels'],
                'attribute_order'             => $normalizedAttribute['order'],
                'is_required'                 => $normalizedAttribute['is_required'],
                'additional_properties'       => $additionalProperties,
            ],
            [
                'is_required'           => Type::getType(Type::BOOLEAN),
                'labels'                => Type::getType(Type::JSON_ARRAY),
                'additional_properties' => Type::getType(Type::JSON_ARRAY),
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
            reference_entity_identifier,
            labels,
            attribute_type,
            attribute_order,
            is_required,
            value_per_channel,
            value_per_locale,
            additional_properties
        FROM akeneo_reference_entity_attribute
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

        return $this->attributeHydratorRegistry->getHydrator($result)->hydrate($result);
    }

    /**
     * @param ReferenceEntityIdentifier $referenceEntityIdentifier
     *
     * @return AbstractAttribute[]
     * @throws DBALException
     */
    public function findByReferenceEntity(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        $fetch = <<<SQL
        SELECT
            identifier,
            code,
            reference_entity_identifier,
            labels,
            attribute_type,
            attribute_order,
            is_required,
            value_per_channel,
            value_per_locale,
            additional_properties
        FROM akeneo_reference_entity_attribute
        WHERE reference_entity_identifier = :reference_entity_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            [
                'reference_entity_identifier' => $referenceEntityIdentifier,
            ]
        );
        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $attributes = [];
        foreach ($results as $result) {
            $attributes[] = $this->attributeHydratorRegistry
                ->getHydrator($result)
                ->hydrate($result);
        }

        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function countByReferenceEntity(ReferenceEntityIdentifier $referenceEntityIdentifier): int
    {
        $fetch = <<<SQL
        SELECT COUNT(*)
        FROM akeneo_reference_entity_attribute
        WHERE reference_entity_identifier = :reference_entity_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            ['reference_entity_identifier' => $referenceEntityIdentifier,]
        );
        $count = $statement->fetchColumn();

        return intval($count);
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

        return $normalizedAttribute;
    }

    /**
     * @throws AttributeNotFoundException
     * @throws DBALException
     */
    public function deleteByIdentifier(AttributeIdentifier $attributeIdentifier): void
    {
        $referenceEntityIdentifier = $this->getReferenceEntityIdentifier($attributeIdentifier);

        $this->eventDispatcher->dispatch(
            new BeforeAttributeDeletedEvent($referenceEntityIdentifier, $attributeIdentifier),
            BeforeAttributeDeletedEvent::class
        );

        $sql = <<<SQL
        DELETE FROM akeneo_reference_entity_attribute
        WHERE identifier = :identifier;
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $sql,
            [
                'identifier' => $attributeIdentifier,
            ]
        );
        if (1 !== $affectedRows) {
            throw AttributeNotFoundException::withIdentifier($attributeIdentifier);
        }

        $this->eventDispatcher->dispatch(
            new AttributeDeletedEvent($referenceEntityIdentifier, $attributeIdentifier),
            AttributeDeletedEvent::class
        );
    }

    public function nextIdentifier(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $attributeCode
    ): AttributeIdentifier {
        return AttributeIdentifier::create(
            (string) $referenceEntityIdentifier,
            (string) $attributeCode,
            Uuid::uuid4()->toString()
        );
    }

    private function getReferenceEntityIdentifier(AttributeIdentifier $attributeIdentifier): ReferenceEntityIdentifier
    {
        $query = <<<SQL
            SELECT reference_entity_identifier
            FROM akeneo_reference_entity_attribute
            WHERE identifier = :identifier
SQL;
        $statement = $this->sqlConnection->executeQuery($query, ['identifier' => (string) $attributeIdentifier]);
        $result = $statement->fetch();
        if (false === $result) {
            throw AttributeNotFoundException::withIdentifier($attributeIdentifier);
        }

        return ReferenceEntityIdentifier::fromString($result['reference_entity_identifier']);
    }
}
