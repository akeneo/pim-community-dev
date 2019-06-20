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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\Hydrator;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorAssetFamily;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ConnectorAssetFamilyHydrator
{
    /** @var AbstractPlatform */
    private $platform;

    public function __construct(
        Connection $connection
    ) {
        $this->platform = $connection->getDatabasePlatform();
    }

    public function hydrate(array $row): ConnectorAssetFamily
    {
        $labels = Type::getType(Type::JSON_ARRAY)
            ->convertToPHPValue($row['labels'], $this->platform);
        $identifier = Type::getType(Type::STRING)
            ->convertToPHPValue($row['identifier'], $this->platform);
        $imageKey = Type::getType(Type::STRING)
            ->convertToPHPValue($row['image_file_key'], $this->platform);
        $imageFilename = Type::getType(Type::STRING)
            ->convertToPHPValue($row['image_original_filename'], $this->platform);

        $image = Image::createEmpty();

        if (isset($row['image_file_key'])) {
            $file = new FileInfo();
            $file->setKey($imageKey);
            $file->setOriginalFilename($imageFilename);
            $image = Image::fromFileInfo($file);
        }

        $connectorAssetFamily = new ConnectorAssetFamily(
            AssetFamilyIdentifier::fromString($identifier),
            LabelCollection::fromArray($labels),
            $image
        );

        return $connectorAssetFamily;
    }
}
