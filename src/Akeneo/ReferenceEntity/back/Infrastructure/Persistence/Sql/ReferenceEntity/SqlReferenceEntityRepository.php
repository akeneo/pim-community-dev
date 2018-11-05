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
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityNotFoundException;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlReferenceEntityRepository implements ReferenceEntityRepositoryInterface
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
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function create(ReferenceEntity $referenceEntity): void
    {
        $serializedLabels = $this->getSerializedLabels($referenceEntity);
        $insert = <<<SQL
        INSERT INTO akeneo_reference_entity_reference_entity (identifier, labels) VALUES (:identifier, :labels);
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $insert,
            [
                'identifier' => (string) $referenceEntity->getIdentifier(),
                'labels' => $serializedLabels
            ]
        );
        if ($affectedRows !== 1) {
            throw new \RuntimeException(
                sprintf('Expected to create one reference entity, but %d were affected', $affectedRows)
            );
        }
    }

    /**
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function update(ReferenceEntity $referenceEntity): void
    {
        $serializedLabels = $this->getSerializedLabels($referenceEntity);
        $update = <<<SQL
        UPDATE akeneo_reference_entity_reference_entity
        SET labels = :labels, image = :image
        WHERE identifier = :identifier;
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $update,
            [
                'identifier' => (string) $referenceEntity->getIdentifier(),
                'labels' => $serializedLabels,
                'image' => $referenceEntity->getImage()->isEmpty() ? null : $referenceEntity->getImage()->getKey()
            ]
        );

        if ($affectedRows > 1) {
            throw new \RuntimeException(
                sprintf('Expected to update one reference entity, but %d rows were affected.', $affectedRows)
            );
        }
    }

    public function getByIdentifier(ReferenceEntityIdentifier $identifier): ReferenceEntity
    {
        $fetch = <<<SQL
        SELECT ee.identifier, ee.labels, fi.image
        FROM akeneo_reference_entity_reference_entity ee
        LEFT JOIN (
          SELECT file_key, JSON_OBJECT("file_key", file_key, "original_filename", original_filename) as image
          FROM akeneo_file_storage_file_info
        ) AS fi ON fi.file_key = ee.image
        WHERE identifier = :identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            ['identifier' => (string) $identifier]
        );
        $result = $statement->fetch();
        $statement->closeCursor();

        if (!$result) {
            throw ReferenceEntityNotFoundException::withIdentifier($identifier);
        }

        return $this->hydrateReferenceEntity(
            $result['identifier'],
            $result['labels'],
            null !== $result['image'] ? json_decode($result['image'], true) : null
        );
    }

    public function all(): \Iterator
    {
        $selectAllQuery = <<<SQL
        SELECT identifier, labels
        FROM akeneo_reference_entity_reference_entity;
SQL;
        $statement = $this->sqlConnection->executeQuery($selectAllQuery);
        $results = $statement->fetchAll();
        $statement->closeCursor();

        foreach ($results as $result) {
            yield $this->hydrateReferenceEntity($result['identifier'], $result['labels'], null);
        }
    }

    public function deleteByIdentifier(ReferenceEntityIdentifier $identifier): void
    {
        $sql = <<<SQL
        DELETE FROM akeneo_reference_entity_reference_entity
        WHERE identifier = :identifier;
SQL;

        $affectedRows = $this->sqlConnection->executeUpdate(
            $sql,
            [
                'identifier' => $identifier
            ]
        );

        if (1 !== $affectedRows) {
            throw ReferenceEntityNotFoundException::withIdentifier($identifier);
        }
    }

    public function count(): int
    {
        $query = <<<SQL
        SELECT COUNT(*) as total
        FROM akeneo_reference_entity_reference_entity
SQL;
        $statement = $this->sqlConnection->executeQuery($query);
        $result = $statement->fetch();

        return intval($result['total']);
    }

    private function hydrateReferenceEntity(
        string $identifier,
        string $normalizedLabels,
        ?array $image
    ): ReferenceEntity {
        $platform = $this->sqlConnection->getDatabasePlatform();

        $labels = json_decode($normalizedLabels, true);
        $identifier = Type::getType(Type::STRING)->convertToPhpValue($identifier, $platform);
        $entityImage = $this->hydrateImage($image);

        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString($identifier),
            $labels,
            $entityImage
        );

        return $referenceEntity;
    }

    private function getSerializedLabels(ReferenceEntity $referenceEntity): string
    {
        $labels = [];
        foreach ($referenceEntity->getLabelCodes() as $localeCode) {
            $labels[$localeCode] = $referenceEntity->getLabel($localeCode);
        }

        return json_encode($labels);
    }

    private function hydrateImage(?array $imageData): Image
    {
        $image = Image::createEmpty();

        if (null !== $imageData) {
            $file = new FileInfo();
            $file->setKey($imageData['file_key']);
            $file->setOriginalFilename($imageData['original_filename']);
            $image = Image::fromFileInfo($file);
        }

        return $image;
    }
}
