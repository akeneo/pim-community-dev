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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record;

use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordItemsForIdentifiersAndQueryInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordItem;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\SqlFindRequiredValueKeyCollectionForChannelAndLocale;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 *
 * Find record items for the given record identifiers & the given record query.
 * Note that this query searches only records with the same reference entity.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindRecordItemsForIdentifiersAndQuery implements FindRecordItemsForIdentifiersAndQueryInterface
{
    /** @var Connection */
    private $sqlConnection;

    /** @var SqlFindRequiredValueKeyCollectionForChannelAndLocale */
    private $findRequiredValueKeyCollectionForChannelAndLocale;

    public function __construct(
        Connection $sqlConnection,
        SqlFindRequiredValueKeyCollectionForChannelAndLocale $findRequiredValueKeyCollectionForChannelAndLocale
    ) {
        $this->sqlConnection = $sqlConnection;
        $this->findRequiredValueKeyCollectionForChannelAndLocale = $findRequiredValueKeyCollectionForChannelAndLocale;
    }

    /**
     * @return string[]
     */
    public function __invoke(array $identifiers, RecordQuery $query): array
    {
        $sqlQuery = <<<SQL
        SELECT ee.identifier, ee.reference_entity_identifier, ee.code, ee.labels, fi.image, ee.value_collection
        FROM akeneo_reference_entity_record AS ee
        LEFT JOIN (
          SELECT file_key, JSON_OBJECT("file_key", file_key, "original_filename", original_filename) as image
          FROM akeneo_file_storage_file_info
        ) AS fi ON fi.file_key = ee.image
        WHERE identifier IN (:identifiers)
        ORDER BY FIELD(identifier, :identifiers);
SQL;

        $statement = $this->sqlConnection->executeQuery($sqlQuery, [
            'identifiers' => $identifiers
        ], ['identifiers' => Connection::PARAM_STR_ARRAY]);
        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $referenceEntityFilter = $query->getFilter('reference_entity');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityFilter['value']);
        $channelIdentifier = ChannelIdentifier::fromCode($query->getChannel());
        $localeIdentifier = LocaleIdentifier::fromCode($query->getLocale());

        /** @var ValueKeyCollection $requiredValueKeyCollection */
        $requiredValueKeyCollection = ($this->findRequiredValueKeyCollectionForChannelAndLocale)(
            $referenceEntityIdentifier,
            $channelIdentifier,
            $localeIdentifier
        );
        $requiredValueKeys = $requiredValueKeyCollection->normalize();

        $recordItems = [];
        foreach ($results as $result) {
            $image = null !== $result['image'] ? json_decode($result['image'], true) : null;
            $image = null !== $result['image'] ? ['filePath' => $image['file_key'], 'originalFilename' => $image['original_filename']] : null;

            $valueCollection = $this->cleanValues($result['value_collection']);
            $completeness = ['complete' => 0, 'required' => 0];

            if (count($requiredValueKeys) > 0) {
                $existingValueKeys = array_keys($valueCollection);
                $completeness['complete'] = count(array_intersect($requiredValueKeys, $existingValueKeys));
                $completeness['required'] = count($requiredValueKeys);
            }

            $recordItems[] = $this->hydrateRecordItem(
                $result['identifier'],
                $result['reference_entity_identifier'],
                $result['code'],
                $image,
                $result['labels'],
                $valueCollection,
                $completeness
            );
        }

        return $recordItems;
    }

    private function hydrateRecordItem(
        string $identifier,
        string $referenceEntityIdentifier,
        string $code,
        ?array $image,
        string $normalizedLabels,
        array $values,
        array $completeness
    ): RecordItem {
        $platform = $this->sqlConnection->getDatabasePlatform();

        $labels = json_decode($normalizedLabels, true);
        $identifier = Type::getType(Type::STRING)->convertToPHPValue($identifier, $platform);
        $referenceEntityIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($referenceEntityIdentifier, $platform);
        $code = Type::getType(Type::STRING)->convertToPHPValue($code, $platform);

        $recordItem = new RecordItem();
        $recordItem->identifier = $identifier;
        $recordItem->referenceEntityIdentifier = $referenceEntityIdentifier;
        $recordItem->code = $code;
        $recordItem->labels = $labels;
        $recordItem->image = $image;
        $recordItem->values = $values;
        $recordItem->completeness = $completeness;

        return $recordItem;
    }

    private function cleanValues(string $values): array
    {
        $cleanValues = strip_tags(html_entity_decode(str_replace(["\r", "\n"], ' ', $values)));

        return json_decode($cleanValues, true);
    }
}
