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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\ReferenceEntity\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\ConnectorReferenceEntity;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ConnectorReferenceEntityHydrator
{
    /** @var AbstractPlatform */
    private $platform;

    public function __construct(
        Connection $connection
    ) {
        $this->platform = $connection->getDatabasePlatform();
    }

    public function hydrate(array $row): ConnectorReferenceEntity
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

        $connectorReferenceEntity = new ConnectorReferenceEntity(
            ReferenceEntityIdentifier::fromString($identifier),
            LabelCollection::fromArray($labels),
            $image
        );

        return $connectorReferenceEntity;
    }
}
