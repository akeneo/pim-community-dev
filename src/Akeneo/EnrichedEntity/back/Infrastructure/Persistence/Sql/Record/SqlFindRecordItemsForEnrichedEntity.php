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

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Record;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Image;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\Record\FindRecordItemsForEnrichedEntityInterface;
use Akeneo\EnrichedEntity\Domain\Query\Record\RecordItem;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindRecordItemsForEnrichedEntity implements FindRecordItemsForEnrichedEntityInterface
{
    /** @var Connection */
    private $sqlConnection;

    /**
     * @param Connection                $sqlConnection
     * @param RecordRepositoryInterface $recordRepository
     */
    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(EnrichedEntityIdentifier $identifier): array
    {
        $query = <<<SQL
        SELECT ee.identifier, ee.enriched_entity_identifier, ee.code, ee.labels, fi.image
        FROM akeneo_enriched_entity_record AS ee
        LEFT JOIN (
          SELECT file_key, JSON_OBJECT("file_key", file_key, "original_filename", original_filename) as image
          FROM akeneo_file_storage_file_info
        ) AS fi ON fi.file_key = ee.image
        WHERE enriched_entity_identifier = :enriched_entity_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'enriched_entity_identifier' => (string) $identifier,
        ]);
        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $statement->closeCursor();

        $recordItems = [];

        foreach ($results as $result) {
            $image = null !== $result['image'] ? json_decode($result['image'], true) : null;
            $recordItems[] = $this->hydrateRecordItem(
                $result['identifier'],
                $result['enriched_entity_identifier'],
                $result['code'],
                $image,
                $result['labels']
            );
        }

        return $recordItems;
    }

    private function hydrateRecordItem(
        string $identifier,
        string $enrichedEntityIdentifier,
        string $code,
        ?array $image,
        string $normalizedLabels
    ): RecordItem {
        $platform = $this->sqlConnection->getDatabasePlatform();

        $labels = json_decode($normalizedLabels, true);
        $identifier = Type::getType(Type::STRING)->convertToPHPValue($identifier, $platform);
        $enrichedEntityIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($enrichedEntityIdentifier, $platform);
        $code = Type::getType(Type::STRING)->convertToPHPValue($code, $platform);

        $recordImage = Image::createEmpty();

        if (null !== $image) {
            $imageKey = Type::getType(Type::STRING)
                ->convertToPHPValue($image['file_key'], $platform);
            $imageFilename = Type::getType(Type::STRING)
                ->convertToPHPValue($image['original_filename'], $platform);
            $file = new FileInfo();
            $file->setKey($imageKey);
            $file->setOriginalFilename($imageFilename);
            $recordImage = Image::fromFileInfo($file);
        }

        $recordItem = new RecordItem();
        $recordItem->identifier = RecordIdentifier::fromString($identifier);
        $recordItem->enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier);
        $recordItem->code = RecordCode::fromString($code);
        $recordItem->labels = LabelCollection::fromArray($labels);
        $recordItem->image = $recordImage;

        return $recordItem;
    }
}
