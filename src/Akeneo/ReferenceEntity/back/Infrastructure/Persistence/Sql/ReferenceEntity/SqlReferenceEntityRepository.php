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

use Doctrine\DBAL\Exception;
use Akeneo\ReferenceEntity\Domain\Event\ReferenceEntityCreatedEvent;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsImageReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsLabelReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityNotFoundException;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlReferenceEntityRepository implements ReferenceEntityRepositoryInterface
{
    public function __construct(
        private Connection $sqlConnection,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @throws \RuntimeException
     * @throws Exception
     */
    public function create(ReferenceEntity $referenceEntity): void
    {
        $serializedLabels = $this->getSerializedLabels($referenceEntity);
        $insert = <<<SQL
        INSERT INTO akeneo_reference_entity_reference_entity 
            (identifier, labels, attribute_as_label, attribute_as_image) 
        VALUES 
            (:identifier, :labels, :attributeAsLabel, :attributeAsImage);
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $insert,
            [
                'identifier' => (string) $referenceEntity->getIdentifier(),
                'labels' => $serializedLabels,
                'attributeAsLabel' => $referenceEntity->getAttributeAsLabelReference()->normalize(),
                'attributeAsImage' => $referenceEntity->getAttributeAsImageReference()->normalize()
            ]
        );
        if ($affectedRows !== 1) {
            throw new \RuntimeException(
                sprintf('Expected to create one reference entity, but %d were affected', $affectedRows)
            );
        }

        $this->eventDispatcher->dispatch(
            new ReferenceEntityCreatedEvent($referenceEntity->getIdentifier()),
            ReferenceEntityCreatedEvent::class
        );
    }

    /**
     * @throws \RuntimeException
     * @throws Exception
     */
    public function update(ReferenceEntity $referenceEntity): void
    {
        $serializedLabels = $this->getSerializedLabels($referenceEntity);
        $update = <<<SQL
        UPDATE akeneo_reference_entity_reference_entity
        SET 
            labels = :labels, 
            image = :image, 
            attribute_as_label = :attributeAsLabel, 
            attribute_as_image = :attributeAsImage
        WHERE identifier = :identifier;
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $update,
            [
                'identifier' => (string) $referenceEntity->getIdentifier(),
                'labels' => $serializedLabels,
                'image' => $referenceEntity->getImage()->isEmpty() ? null : $referenceEntity->getImage()->getKey(),
                'attributeAsLabel' => $referenceEntity->getAttributeAsLabelReference()->normalize(),
                'attributeAsImage' => $referenceEntity->getAttributeAsImageReference()->normalize(),
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
        SELECT ee.identifier, ee.labels, fi.image, ee.attribute_as_label, ee.attribute_as_image
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
        $result = $statement->fetchAssociative();
        $statement->free();

        if (!$result) {
            throw ReferenceEntityNotFoundException::withIdentifier($identifier);
        }

        return $this->hydrateReferenceEntity(
            $result['identifier'],
            $result['labels'],
            null !== $result['image'] ? json_decode($result['image'], true) : null,
            $result['attribute_as_label'],
            $result['attribute_as_image']
        );
    }

    public function all(): \Iterator
    {
        $selectAllQuery = <<<SQL
        SELECT identifier, labels, attribute_as_label, attribute_as_image
        FROM akeneo_reference_entity_reference_entity;
SQL;
        $statement = $this->sqlConnection->executeQuery($selectAllQuery);
        $results = $statement->fetchAllAssociative();
        $statement->free();

        foreach ($results as $result) {
            yield $this->hydrateReferenceEntity(
                $result['identifier'],
                $result['labels'],
                null,
                $result['attribute_as_label'],
                $result['attribute_as_image']
            );
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
        $result = $statement->fetchAssociative();

        return (int) $result['total'];
    }

    private function hydrateReferenceEntity(
        string $identifier,
        string $normalizedLabels,
        ?array $image,
        ?string $attributeAsLabel,
        ?string $attributeAsImage
    ): ReferenceEntity {
        $platform = $this->sqlConnection->getDatabasePlatform();

        $labels = json_decode($normalizedLabels, true);
        $identifier = Type::getType(Types::STRING)->convertToPhpValue($identifier, $platform);
        $entityImage = $this->hydrateImage($image);

        return ReferenceEntity::createWithAttributes(
            ReferenceEntityIdentifier::fromString($identifier),
            $labels,
            $entityImage,
            AttributeAsLabelReference::createFromNormalized($attributeAsLabel),
            AttributeAsImageReference::createFromNormalized($attributeAsImage)
        );
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
