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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\FindReferenceEntityItemsInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityItem;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * TODO: think about cursor/es index
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindReferenceEntityItems implements FindReferenceEntityItemsInterface
{
    /** @var Connection */
    private $sqlConnection;

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
        $referenceEntityItems = [];
        foreach ($results as $result) {
            $referenceEntityItems[] = $this->hydrateReferenceEntityItem(
                $result['identifier'],
                $result['labels'],
                $result['image']
            );
        }

        return $referenceEntityItems;
    }

    private function fetchResults(): array
    {
        $query = <<<SQL
        SELECT ee.identifier, ee.labels, fi.image
        FROM akeneo_reference_entity_reference_entity AS ee
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

    private function hydrateReferenceEntityItem(
        string $identifier,
        string $normalizedLabels,
        ?string $rawFile
    ): ReferenceEntityItem {
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

        $referenceEntityItem = new ReferenceEntityItem();
        $referenceEntityItem->identifier = ReferenceEntityIdentifier::fromString($identifier);
        $referenceEntityItem->labels = LabelCollection::fromArray($labels);
        $referenceEntityItem->image = $image;

        return $referenceEntityItem;
    }
}
