<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindSearchableAssetsInterface;
use Akeneo\AssetManager\Domain\Query\Asset\SearchableAssetItem;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindSearchableAssets implements FindSearchableAssetsInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function byAssetIdentifier(AssetIdentifier $assetIdentifier): ?SearchableAssetItem
    {
        $sqlQuery = <<<SQL
        SELECT ass.identifier, ass.asset_family_identifier, ass.code, ass.value_collection, assfam.attribute_as_label, ass.updated_at
        FROM akeneo_asset_manager_asset ass
        INNER JOIN akeneo_asset_manager_asset_family assfam ON assfam.identifier = ass.asset_family_identifier
        WHERE ass.identifier = :asset_identifier;
SQL;

        $statement = $this->connection->executeQuery($sqlQuery, ['asset_identifier' => (string) $assetIdentifier]);
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        return !$result ? null : $this->hydrateAssetToIndex(
            $result['identifier'],
            $result['asset_family_identifier'],
            $result['code'],
            $result['updated_at'],
            ValuesDecoder::decode($result['value_collection']),
            $result['attribute_as_label']
        );
    }

    public function byAssetIdentifiers(array $assetIdentifiers): \Iterator
    {
        $sqlQuery = <<<SQL
        SELECT asset.identifier, asset.asset_family_identifier, asset.code, asset.value_collection, asset_family.attribute_as_label
        FROM akeneo_asset_manager_asset asset
        INNER JOIN akeneo_asset_manager_asset_family asset_family
            ON asset_family.identifier = asset.asset_family_identifier
        WHERE asset.identifier IN (:asset_identifiers)
SQL;

        $statement = $this->connection->executeQuery(
            $sqlQuery,
            ['asset_identifiers' => $assetIdentifiers],
            ['asset_identifiers' => Connection::PARAM_STR_ARRAY]
        );

        while (false !== $result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            yield $this->hydrateAssetToIndex(
                $result['identifier'],
                $result['asset_family_identifier'],
                $result['code'],
                ValuesDecoder::decode($result['value_collection']),
                $result['attribute_as_label']
            );
        }
    }

    /** @TODO pull up remove this function in master */
    public function byAssetFamilyIdentifier(AssetFamilyIdentifier $assetFamilyIdentifier): \Iterator
    {
        $sqlQuery = <<<SQL
        SELECT ass.identifier, ass.asset_family_identifier, ass.code, ass.value_collection, assfam.attribute_as_label, ass.updated_at
        FROM akeneo_asset_manager_asset ass
        INNER JOIN akeneo_asset_manager_asset_family assfam ON assfam.identifier = ass.asset_family_identifier
        WHERE assfam.identifier = :asset_family_identifier;
SQL;

        $statement = $this->connection->executeQuery($sqlQuery, ['asset_family_identifier' => (string) $assetFamilyIdentifier]);
        while (false !== $result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            yield $this->hydrateAssetToIndex(
                $result['identifier'],
                $result['asset_family_identifier'],
                $result['code'],
                $result['updated_at'],
                ValuesDecoder::decode($result['value_collection']),
                $result['attribute_as_label']
            );
        }
    }

    private function hydrateAssetToIndex(
        string $identifier,
        string $assetFamilyIdentifier,
        string $code,
        string $updatedAt,
        array $values,
        ?string $attributeAsLabel
    ): SearchableAssetItem {
        $platform = $this->connection->getDatabasePlatform();

        $identifier = Type::getType(Type::STRING)->convertToPHPValue($identifier, $platform);
        $assetFamilyIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($assetFamilyIdentifier, $platform);
        $code = Type::getType(Type::STRING)->convertToPHPValue($code, $platform);
        $attributeAsLabel = Type::getType(Type::STRING)->convertToPHPValue($attributeAsLabel, $platform);
        $updatedAt = Type::getType(Type::DATETIME_IMMUTABLE)->convertToPHPValue($updatedAt, $platform);

        $assetItem = new SearchableAssetItem();
        $assetItem->identifier = $identifier;
        $assetItem->assetFamilyIdentifier = $assetFamilyIdentifier;
        $assetItem->code = $code;
        $assetItem->labels = $this->getLabels($attributeAsLabel, $values);
        $assetItem->values = $values;
        $assetItem->updatedAt = $updatedAt;

        return $assetItem;
    }

    private function getLabels(?string $attributeAsLabelIdentifier, array $values): array
    {
        if (null === $attributeAsLabelIdentifier) {
            return [];
        }

        $labels = array_reduce(
            $values,
            function (array $labels, array $value) use ($attributeAsLabelIdentifier) {
                if ($value['attribute'] === $attributeAsLabelIdentifier) {
                    $labels[$value['locale']] = $value['data'];
                }

                return $labels;
            },
            []
        );

        return $labels;
    }
}
