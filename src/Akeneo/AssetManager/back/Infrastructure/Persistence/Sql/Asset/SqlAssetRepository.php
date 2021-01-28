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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset;

use Akeneo\AssetManager\Domain\Event\AssetCreatedEvent;
use Akeneo\AssetManager\Domain\Event\AssetDeletedEvent;
use Akeneo\AssetManager\Domain\Event\AssetFamilyAssetsDeletedEvent;
use Akeneo\AssetManager\Domain\Event\AssetUpdatedEvent;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindIdentifiersByAssetFamilyAndCodesInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\FindValueKeyCollectionInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\FindValueKeysByAttributeTypeInterface;
use Akeneo\AssetManager\Domain\Repository\AssetNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetHydratorInterface;
use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\CountAssets;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlAssetRepository implements AssetRepositoryInterface
{
    /** @var Connection */
    private $sqlConnection;

    /** @var AssetHydratorInterface */
    private $assetHydrator;

    /** @var FindValueKeyCollectionInterface */
    private $findValueKeyCollection;

    /** @var FindAttributesIndexedByIdentifierInterface */
    private $findAttributesIndexedByIdentifier;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var FindIdentifiersByAssetFamilyAndCodesInterface */
    private $findIdentifiersByAssetFamilyAndCodes;

    /** @var FindValueKeysByAttributeTypeInterface */
    private $findValueKeysByAttributeType;

    /** @TODO pull up Replace by Akeneo\AssetManager\Domain\Query\Asset\CountAssetsInterface */
    /** @var CountAssets|null */
    private $countAssets;

    /** @TODO pull up remove optionnal parameter */
    public function __construct(
        Connection $sqlConnection,
        AssetHydratorInterface $assetHydrator,
        FindValueKeyCollectionInterface $findValueKeyCollection,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        EventDispatcherInterface $eventDispatcher,
        FindIdentifiersByAssetFamilyAndCodesInterface $findIdentifiersByAssetFamilyAndCodes,
        FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType,
        CountAssets $countAssets = null
    ) {
        $this->sqlConnection = $sqlConnection;
        $this->assetHydrator = $assetHydrator;
        $this->findValueKeyCollection = $findValueKeyCollection;
        $this->findAttributesIndexedByIdentifier = $findAttributesIndexedByIdentifier;
        $this->eventDispatcher = $eventDispatcher;
        $this->findIdentifiersByAssetFamilyAndCodes = $findIdentifiersByAssetFamilyAndCodes;
        $this->findValueKeysByAttributeType = $findValueKeysByAttributeType;
        $this->countAssets = $countAssets;
    }

    /** @TODO pull up remove SELECT COUNT(*) query */
    public function count(): int
    {
        if ($this->countAssets) {
            return $this->countAssets->all();
        }

        $sql = 'SELECT COUNT(*) FROM akeneo_asset_manager_asset';
        $statement = $this->sqlConnection->executeQuery($sql);
        $count = (int) $statement->fetchColumn();

        return $count;
    }

    public function create(Asset $asset): void
    {
        $valueCollection = $asset->getValues()->normalize();

        $insert = <<<SQL
        INSERT INTO akeneo_asset_manager_asset
            (identifier, code, asset_family_identifier, value_collection)
        VALUES (:identifier, :code, :asset_family_identifier, :value_collection);
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $insert,
            [
                'identifier' => (string) $asset->getIdentifier(),
                'code' => (string) $asset->getCode(),
                'asset_family_identifier' => (string) $asset->getAssetFamilyIdentifier(),
                'value_collection' => $valueCollection,
            ],
            [
                'value_collection' => Type::JSON_ARRAY,
            ]
        );
        if ($affectedRows > 1) {
            throw new \RuntimeException(
                sprintf('Expected to create one asset, but %d rows were affected', $affectedRows)
            );
        }

        $this->eventDispatcher->dispatch(
            AssetCreatedEvent::class,
            new AssetCreatedEvent(
                $asset->getIdentifier(),
                $asset->getCode(),
                $asset->getAssetFamilyIdentifier()
            )
        );
    }

    public function update(Asset $asset): void
    {
        $valueCollection = $asset->getValues()->normalize();

        $update = <<<SQL
        UPDATE akeneo_asset_manager_asset
        SET value_collection = :value_collection
        WHERE identifier = :identifier;
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $update,
            [
                'identifier' => $asset->getIdentifier(),
                'value_collection' => $valueCollection,
            ],
            [
                'value_collection' => Type::JSON_ARRAY,
            ]
        );

        if ($affectedRows > 1) {
            throw new \RuntimeException(
                sprintf('Expected to update one asset, but %d rows were affected', $affectedRows)
            );
        }

        $this->eventDispatcher->dispatch(
            AssetUpdatedEvent::class,
            new AssetUpdatedEvent($asset->getIdentifier(), $asset->getCode(), $asset->getAssetFamilyIdentifier())
        );
    }

    public function getByAssetFamilyAndCode(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetCode $code
    ): Asset {
        $fetch = <<<SQL
        SELECT identifier, code, asset_family_identifier, value_collection
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
        $result = $statement->fetch();

        if (!$result) {
            throw AssetNotFoundException::withAssetFamilyAndCode($assetFamilyIdentifier, $code);
        }

        return $this->hydrateAsset($result);
    }

    public function getByIdentifier(AssetIdentifier $identifier): Asset
    {
        $fetch = <<<SQL
        SELECT asset.identifier, asset.code, asset.asset_family_identifier, asset.value_collection, reference.attribute_as_label, reference.attribute_as_main_media
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
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        if (!$result) {
            throw AssetNotFoundException::withIdentifier($identifier);
        }

        return $this->hydrateAsset($result);
    }

    public function deleteByAssetFamily(
        AssetFamilyIdentifier $assetFamilyIdentifier
    ): void {
        $sql = <<<SQL
        DELETE FROM akeneo_asset_manager_asset
        WHERE asset_family_identifier = :asset_family_identifier;
SQL;
        $this->sqlConnection->executeUpdate(
            $sql,
            [
                'asset_family_identifier' => (string) $assetFamilyIdentifier,
            ]
        );

        $this->eventDispatcher->dispatch(
            AssetFamilyAssetsDeletedEvent::class,
            new AssetFamilyAssetsDeletedEvent($assetFamilyIdentifier)
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
        $affectedRows = $this->sqlConnection->executeUpdate(
            $sql,
            [
                'code' => (string) $code,
                'asset_family_identifier' => (string) $assetFamilyIdentifier,
            ]
        );

        if (0 === $affectedRows) {
            throw new AssetNotFoundException();
        }

        $this->eventDispatcher->dispatch(
            AssetDeletedEvent::class,
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

    /** @TODO pull up remove SELECT COUNT(*) query */
    public function countByAssetFamily(AssetFamilyIdentifier $assetFamilyIdentifier): int
    {
        if ($this->countAssets) {
            return $this->countAssets->forAssetFamily($assetFamilyIdentifier);
        }

        $fetch = <<<SQL
        SELECT COUNT(*)
        FROM akeneo_asset_manager_asset
        WHERE asset_family_identifier = :asset_family_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            ['asset_family_identifier' => $assetFamilyIdentifier,]
        );
        $count = $statement->fetchColumn();

        return intval($count);
    }

    private function getAssetFamilyIdentifier($result): AssetFamilyIdentifier
    {
        if (!isset($result['asset_family_identifier'])) {
            throw new \LogicException('The asset should have an asset family identifier');
        }
        $normalizedAssetFamilyIdentifier = Type::getType(Type::STRING)->convertToPHPValue(
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
}
