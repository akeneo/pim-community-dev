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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\AssetManager\Domain\Event\AttributeDeletedEvent;
use Akeneo\AssetManager\Domain\Event\BeforeAttributeDeletedEvent;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Repository\AttributeNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute\Hydrator\AttributeHydratorRegistry;
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
        INSERT INTO akeneo_asset_manager_attribute (
            identifier,
            code,
            asset_family_identifier,
            labels,
            attribute_type,
            attribute_order,
            is_required,
            is_read_only,
            value_per_channel,
            value_per_locale,
            additional_properties
        )
        VALUES (
            :identifier,
            :code,
            :asset_family_identifier,
            :labels,
            :attribute_type,
            :attribute_order,
            :is_required,
            :is_read_only,
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
                'asset_family_identifier' => $normalizedAttribute['asset_family_identifier'],
                'labels'                      => json_encode($normalizedAttribute['labels']),
                'attribute_type'              => $normalizedAttribute['type'],
                'attribute_order'             => $normalizedAttribute['order'],
                'is_required'                 => $normalizedAttribute['is_required'],
                'is_read_only'                => $normalizedAttribute['is_read_only'],
                'value_per_channel'           => $normalizedAttribute['value_per_channel'],
                'value_per_locale'            => $normalizedAttribute['value_per_locale'],
                'additional_properties'       => json_encode($additionalProperties),
            ],
            [
                'is_required'       => Type::getType(Type::BOOLEAN),
                'is_read_only'      => Type::getType(Type::BOOLEAN),
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
        UPDATE akeneo_asset_manager_attribute SET
            labels = :labels,
            attribute_order = :attribute_order,
            is_required = :is_required,
            is_read_only = :is_read_only,
            additional_properties = :additional_properties
        WHERE identifier = :identifier;
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $update,
            [
                'identifier'                  => $normalizedAttribute['identifier'],
                'asset_family_identifier' => $normalizedAttribute['asset_family_identifier'],
                'labels'                      => $normalizedAttribute['labels'],
                'attribute_order'             => $normalizedAttribute['order'],
                'is_required'                 => $normalizedAttribute['is_required'],
                'is_read_only'                => $normalizedAttribute['is_read_only'],
                'additional_properties'       => $additionalProperties,
            ],
            [
                'is_required'           => Type::getType(Type::BOOLEAN),
                'is_read_only'          => Type::getType(Type::BOOLEAN),
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
            asset_family_identifier,
            labels,
            attribute_type,
            attribute_order,
            is_required,
            is_read_only,
            value_per_channel,
            value_per_locale,
            additional_properties
        FROM akeneo_asset_manager_attribute
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
     * @throws AttributeNotFoundException
     * @throws DBALException
     */
    public function getByCodeAndAssetFamilyIdentifier(AttributeCode $code, AssetFamilyIdentifier $assetFamilyIdentifier): AbstractAttribute
    {
        $fetch = <<<SQL
        SELECT
            identifier,
            code,
            asset_family_identifier,
            labels,
            attribute_type,
            attribute_order,
            is_required,
            is_read_only,
            value_per_channel,
            value_per_locale,
            additional_properties
        FROM akeneo_asset_manager_attribute
        WHERE asset_family_identifier = :asset_family_identifier
            AND code = :code;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            [
                'asset_family_identifier' => $assetFamilyIdentifier,
                'code' => $code
            ]
        );
        $result = $statement->fetch();

        if (!$result) {
            throw AttributeNotFoundException::withAssetFamilyAndAttributeCode($assetFamilyIdentifier, $code);
        }

        return $this->attributeHydratorRegistry->getHydrator($result)->hydrate($result);
    }

    /**
     * @param AssetFamilyIdentifier $assetFamilyIdentifier
     *
     * @return AbstractAttribute[]
     * @throws DBALException
     */
    public function findByAssetFamily(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        $fetch = <<<SQL
        SELECT
            identifier,
            code,
            asset_family_identifier,
            labels,
            attribute_type,
            attribute_order,
            is_required,
            is_read_only,
            value_per_channel,
            value_per_locale,
            additional_properties
        FROM akeneo_asset_manager_attribute
        WHERE asset_family_identifier = :asset_family_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            [
                'asset_family_identifier' => $assetFamilyIdentifier,
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
    public function countByAssetFamily(AssetFamilyIdentifier $assetFamilyIdentifier): int
    {
        $fetch = <<<SQL
        SELECT COUNT(*)
        FROM akeneo_asset_manager_attribute
        WHERE asset_family_identifier = :asset_family_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            ['asset_family_identifier' => $assetFamilyIdentifier,]
        );
        $count = $statement->fetchColumn();

        return intval($count);
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

        return $normalizedAttribute;
    }

    /**
     * @throws AttributeNotFoundException
     * @throws DBALException
     */
    public function deleteByIdentifier(AttributeIdentifier $attributeIdentifier): void
    {
        $assetFamilyIdentifier = $this->getAssetFamilyIdentifier($attributeIdentifier);

        $this->eventDispatcher->dispatch(
            new BeforeAttributeDeletedEvent($assetFamilyIdentifier, $attributeIdentifier),
            BeforeAttributeDeletedEvent::class
        );

        $sql = <<<SQL
        DELETE FROM akeneo_asset_manager_attribute
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
            new AttributeDeletedEvent($assetFamilyIdentifier, $attributeIdentifier),
            AttributeDeletedEvent::class
        );
    }

    public function nextIdentifier(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeCode $attributeCode
    ): AttributeIdentifier {
        return AttributeIdentifier::create(
            (string) $assetFamilyIdentifier,
            (string) $attributeCode,
            Uuid::uuid4()->toString()
        );
    }

    private function getAssetFamilyIdentifier(AttributeIdentifier $attributeIdentifier): AssetFamilyIdentifier
    {
        $query = <<<SQL
            SELECT asset_family_identifier
            FROM akeneo_asset_manager_attribute
            WHERE identifier = :identifier
SQL;
        $statement = $this->sqlConnection->executeQuery($query, ['identifier' => (string) $attributeIdentifier]);
        $result = $statement->fetch();
        if (false === $result) {
            throw AttributeNotFoundException::withIdentifier($attributeIdentifier);
        }

        return AssetFamilyIdentifier::fromString($result['asset_family_identifier']);
    }
}
