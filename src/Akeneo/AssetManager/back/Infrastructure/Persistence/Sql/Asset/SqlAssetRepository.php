<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset;

use Akeneo\AssetManager\Domain\Event\AssetDeletedEvent;
use Akeneo\AssetManager\Domain\Event\AssetsDeletedEvent;
use Akeneo\AssetManager\Domain\Event\DomainEvent;
use Akeneo\AssetManager\Domain\Exception\AssetAlreadyExistsError;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\CountAssetsInterface;
use Akeneo\AssetManager\Domain\Query\Asset\FindIdentifiersByAssetFamilyAndCodesInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\FindValueKeyCollectionInterface;
use Akeneo\AssetManager\Domain\Repository\AssetNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetHydratorInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SqlAssetRepository implements AssetRepositoryInterface
{
    public function __construct(
        private Connection $sqlConnection,
        private AssetHydratorInterface $assetHydrator,
        private FindValueKeyCollectionInterface $findValueKeyCollection,
        private FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        private EventDispatcherInterface $eventDispatcher,
        private FindIdentifiersByAssetFamilyAndCodesInterface $findIdentifiersByAssetFamilyAndCodes,
        private CountAssetsInterface $countAssets,
    ) {
    }

    public function count(): int
    {
        return $this->countAssets->all();
    }

    public function create(Asset $asset): void
    {
        $valueCollection = $asset->getValues()->normalize();

        $insert = <<<SQL
        INSERT INTO akeneo_asset_manager_asset
            (identifier, code, asset_family_identifier, value_collection, created_at, updated_at)
        VALUES (:identifier, :code, :asset_family_identifier, :value_collection, :created_at, :updated_at);
SQL;
        try {
            $affectedRows = $this->sqlConnection->executeStatement(
                $insert,
                [
                    'identifier' => (string) $asset->getIdentifier(),
                    'code' => (string) $asset->getCode(),
                    'asset_family_identifier' => (string) $asset->getAssetFamilyIdentifier(),
                    'value_collection' => $valueCollection,
                    'created_at' => $asset->getCreatedAt(),
                    'updated_at' => $asset->getUpdatedAt(),
                ],
                [
                    'value_collection' => Types::JSON,
                    'created_at' => Types::DATETIME_IMMUTABLE,
                    'updated_at' => Types::DATETIME_IMMUTABLE,
                ]
            );
        } catch (UniqueConstraintViolationException) {
            throw AssetAlreadyExistsError::fromAsset($asset);
        }

        if ($affectedRows > 1) {
            throw new \RuntimeException(
                sprintf('Expected to create one asset, but %d rows were affected', $affectedRows)
            );
        }

        $this->dispatchAssetEvents($asset);
    }

    public function update(Asset $asset): void
    {
        $valueCollection = $asset->getValues()->normalize();

        $update = <<<SQL
        UPDATE akeneo_asset_manager_asset
        SET value_collection = :value_collection,
            updated_at = :updated_at
        WHERE identifier = :identifier;
SQL;
        $affectedRows = $this->sqlConnection->executeStatement(
            $update,
            [
                'identifier' => $asset->getIdentifier(),
                'value_collection' => $valueCollection,
                'updated_at' => $asset->getUpdatedAt(),
            ],
            [
                'value_collection' => Types::JSON,
                'updated_at' => Types::DATETIME_IMMUTABLE
            ]
        );

        if ($affectedRows > 1) {
            throw new \RuntimeException(
                sprintf('Expected to update one asset, but %d rows were affected', $affectedRows)
            );
        }

        $this->dispatchAssetEvents($asset);
    }

    public function getByAssetFamilyAndCode(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetCode $code
    ): Asset {
        $fetch = <<<SQL
        SELECT identifier, code, asset_family_identifier, value_collection, created_at, updated_at
        FROM akeneo_asset_manager_asset
        WHERE code = :code AND asset_family_identifier = :asset_family_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            [
                'code' => (string) $code,
                'asset_family_identifier' => (string) $assetFamilyIdentifier,
            ]
        );
        $result = $statement->fetchAssociative();

        if (!$result) {
            throw AssetNotFoundException::withAssetFamilyAndCode($assetFamilyIdentifier, $code);
        }

        return $this->hydrateAsset($result);
    }

    public function getByIdentifier(AssetIdentifier $identifier): Asset
    {
        $fetch = <<<SQL
        SELECT asset.identifier, asset.code, asset.asset_family_identifier, asset.value_collection, reference.attribute_as_label, reference.attribute_as_main_media, created_at, updated_at
        FROM akeneo_asset_manager_asset AS asset
        INNER JOIN akeneo_asset_manager_asset_family AS reference
            ON reference.identifier = asset.asset_family_identifier
        WHERE asset.identifier = :asset_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            [
                'asset_identifier' => (string) $identifier,
            ]
        );
        $result = $statement->fetchAssociative();

        if (!$result) {
            throw AssetNotFoundException::withIdentifier($identifier);
        }

        return $this->hydrateAsset($result);
    }

    public function deleteByAssetFamilyAndCodes(AssetFamilyIdentifier $assetFamilyIdentifier, array $assetCodes): void
    {
        $identifiers = $this->findIdentifiersByAssetFamilyAndCodes->find($assetFamilyIdentifier, $assetCodes);

        $sql = <<<SQL
        DELETE FROM akeneo_asset_manager_asset
        WHERE code IN (:codes) AND asset_family_identifier = :asset_family_identifier;
SQL;
        $this->sqlConnection->executeStatement(
            $sql,
            [
                'codes' => $assetCodes,
                'asset_family_identifier' => (string) $assetFamilyIdentifier,
            ],
            [
                'codes' => Connection::PARAM_STR_ARRAY
            ]
        );

        $assetCodeDeleted = array_filter($assetCodes, fn ($assetCode) => array_key_exists($assetCode->normalize(), $identifiers));

        $this->eventDispatcher->dispatch(
            new AssetsDeletedEvent(
                $assetFamilyIdentifier,
                $assetCodeDeleted,
            )
        );
    }

    public function deleteByAssetFamilyAndCode(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetCode $code
    ): void {
        $identifiers = $this->findIdentifiersByAssetFamilyAndCodes->find($assetFamilyIdentifier, [$code]);

        $sql = <<<SQL
        DELETE FROM akeneo_asset_manager_asset
        WHERE code = :code AND asset_family_identifier = :asset_family_identifier;
SQL;
        $affectedRowsCount = $this->sqlConnection->executeStatement(
            $sql,
            [
                'code' => (string) $code,
                'asset_family_identifier' => (string) $assetFamilyIdentifier,
            ]
        );

        if (0 === $affectedRowsCount) {
            throw new AssetNotFoundException();
        }

        $this->eventDispatcher->dispatch(
            new AssetDeletedEvent(
                $identifiers[$code->normalize()],
                $code,
                $assetFamilyIdentifier
            )
        );
    }

    public function nextIdentifier(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetCode $code
    ): AssetIdentifier {
        return AssetIdentifier::create(
            (string) $assetFamilyIdentifier,
            (string) $code,
            Uuid::uuid4()->toString()
        );
    }

    private function getAssetFamilyIdentifier($result): AssetFamilyIdentifier
    {
        if (!isset($result['asset_family_identifier'])) {
            throw new \LogicException('The asset should have an asset family identifier');
        }
        $normalizedAssetFamilyIdentifier = Type::getType(Types::STRING)->convertToPHPValue(
            $result['asset_family_identifier'],
            $this->sqlConnection->getDatabasePlatform()
        );

        return AssetFamilyIdentifier::fromString($normalizedAssetFamilyIdentifier);
    }

    private function hydrateAsset($result): Asset
    {
        $assetFamilyIdentifier = $this->getAssetFamilyIdentifier($result);
        $valueKeyCollection = $this->findValueKeyCollection->find($assetFamilyIdentifier);
        $attributesIndexedByIdentifier = $this->findAttributesIndexedByIdentifier->find($assetFamilyIdentifier);

        return $this->assetHydrator->hydrate(
            $result,
            $valueKeyCollection,
            $attributesIndexedByIdentifier
        );
    }

    private function dispatchAssetEvents(Asset $asset)
    {
        foreach ($asset->getRecordedEvents() as $event) {
            if (!$event instanceof DomainEvent) {
                continue;
            }

            $this->eventDispatcher->dispatch($event);
        }

        $asset->clearRecordedEvents();
    }
}
