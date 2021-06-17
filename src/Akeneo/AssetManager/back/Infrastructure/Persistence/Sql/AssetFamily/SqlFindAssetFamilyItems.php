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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyItem;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyItemsInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * TODO: think about cursor/es index
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindAssetFamilyItems implements FindAssetFamilyItemsInterface
{
    private Connection $sqlConnection;

    /**
     * @param Connection $sqlConnection
     */
    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function find(): array
    {
        $results = $this->fetchResults();
        $assetFamilyItems = [];
        foreach ($results as $result) {
            $assetFamilyItems[] = $this->hydrateAssetFamilyItem(
                $result['identifier'],
                $result['labels'],
                $result['image']
            );
        }

        return $assetFamilyItems;
    }

    private function fetchResults(): array
    {
        $query = <<<SQL
        SELECT ee.identifier, ee.labels, fi.image
        FROM akeneo_asset_manager_asset_family AS ee
        LEFT JOIN (
          SELECT file_key, JSON_OBJECT("file_key", file_key, "original_filename", original_filename) as image
          FROM akeneo_file_storage_file_info
        ) AS fi ON fi.file_key = ee.image
SQL;
        $statement = $this->sqlConnection->executeQuery($query);
        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $statement->closeCursor();

        return $results;
    }

    private function hydrateAssetFamilyItem(
        string $identifier,
        string $normalizedLabels,
        ?string $rawFile
    ): AssetFamilyItem {
        $platform = $this->sqlConnection->getDatabasePlatform();

        $labels = Type::getType(Type::JSON_ARRAY)->convertToPHPValue($normalizedLabels, $platform);
        $identifier = Type::getType(Type::STRING)->convertToPHPValue($identifier, $platform);

        $image = Image::createEmpty();
        if (null !== $rawFile) {
            $rawFile = Type::getType(Type::JSON_ARRAY)->convertToPHPValue($rawFile, $platform);
            ;
            $file = new FileInfo();
            $file->setKey($rawFile['file_key']);
            $file->setOriginalFilename($rawFile['original_filename']);
            $image = Image::fromFileInfo($file);
        }

        $assetFamilyItem = new AssetFamilyItem();
        $assetFamilyItem->identifier = AssetFamilyIdentifier::fromString($identifier);
        $assetFamilyItem->labels = LabelCollection::fromArray($labels);
        $assetFamilyItem->image = $image;

        return $assetFamilyItem;
    }
}
