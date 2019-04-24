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
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordItemsForIdentifiersAndQueryInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordItem;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\SqlFindRequiredValueKeyCollectionForChannelAndLocales;
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

    /** @var SqlFindRequiredValueKeyCollectionForChannelAndLocales */
    private $findRequiredValueKeyCollectionForChannelAndLocales;

    public function __construct(
        Connection $sqlConnection,
        SqlFindRequiredValueKeyCollectionForChannelAndLocales $findRequiredValueKeyCollectionForChannelAndLocale
    ) {
        $this->sqlConnection = $sqlConnection;
        $this->findRequiredValueKeyCollectionForChannelAndLocales = $findRequiredValueKeyCollectionForChannelAndLocale;
    }

    public function __invoke(array $identifiers, RecordQuery $query): array
    {
        $normalizedRecordItems = $this->fetchAll($identifiers);
        $requiredValueKeys = $this->getRequiredValueKeys($query);
        $recordItems = $this->hydrateRecordItems($requiredValueKeys, $normalizedRecordItems);

        return $recordItems;
    }

    private function fetchAll(array $identifiers): array
    {
        $sqlQuery = <<<SQL
        SELECT
            record.identifier,
            record.reference_entity_identifier,
            record.code,
            record.value_collection,
            reference.attribute_as_image,
            reference.attribute_as_label
        FROM akeneo_reference_entity_record AS record
        INNER JOIN akeneo_reference_entity_reference_entity AS reference
            ON reference.identifier = record.reference_entity_identifier
        WHERE record.identifier IN (:identifiers)
        ORDER BY FIELD(record.identifier, :identifiers);
SQL;

        $statement = $this->sqlConnection->executeQuery($sqlQuery, [
            'identifiers' => $identifiers,
        ], ['identifiers' => Connection::PARAM_STR_ARRAY]);
        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return $results;
    }

    private function getRequiredValueKeys(RecordQuery $query): ValueKeyCollection
    {
        $referenceEntityFilter = $query->getFilter('reference_entity');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityFilter['value']);
        $channelIdentifier = ChannelIdentifier::fromCode($query->getChannel());
        $localeIdentifiers = LocaleIdentifierCollection::fromNormalized([$query->getLocale()]);

        /** @var ValueKeyCollection $result */
        $result = ($this->findRequiredValueKeyCollectionForChannelAndLocales)(
            $referenceEntityIdentifier,
            $channelIdentifier,
            $localeIdentifiers
        );

        return $result;
    }

    private function hydrateRecordItems(ValueKeyCollection $requiredValueKeys, array $results): array
    {
        $recordItems = [];
        foreach ($results as $result) {
            $recordItems[] = $this->hydrateRecordItem(
                $requiredValueKeys,
                $result
            );
        }

        return $recordItems;
    }

    private function hydrateRecordItem(
        ValueKeyCollection $requiredValueKeyCollection,
        array $normalizedRecordItem
    ): RecordItem {
        $platform = $this->sqlConnection->getDatabasePlatform();

        $identifier = Type::getType(Type::STRING)->convertToPHPValue($normalizedRecordItem['identifier'], $platform);
        $referenceEntityIdentifier = Type::getType(Type::STRING)->convertToPHPValue($normalizedRecordItem['reference_entity_identifier'], $platform);
        $code = Type::getType(Type::STRING)->convertToPHPValue($normalizedRecordItem['code'], $platform);
        $valueCollection = ValuesDecoder::decode($normalizedRecordItem['value_collection']);

        $attributeAsLabel = Type::getType(Type::STRING)->convertToPHPValue($normalizedRecordItem['attribute_as_label'], $platform);
        $labels = $this->getLabels($valueCollection, $attributeAsLabel);
        $attributeAsImage = Type::getType(Type::STRING)->convertToPHPValue($normalizedRecordItem['attribute_as_image'], $platform);
        $image = $this->getImage($valueCollection, $attributeAsImage);

        $recordItem = new RecordItem();
        $recordItem->identifier = $identifier;
        $recordItem->referenceEntityIdentifier = $referenceEntityIdentifier;
        $recordItem->code = $code;
        $recordItem->labels = $labels;
        $recordItem->image = $image;
        $recordItem->values = $valueCollection;
        $recordItem->completeness = $this->getCompleteness($requiredValueKeyCollection, $valueCollection);

        return $recordItem;
    }

    private function getCompleteness(ValueKeyCollection $requiredValueKeys, $valueCollection): array
    {
        $normalizedRequiredValueKeys = $requiredValueKeys->normalize();

        $completeness = ['complete' => 0, 'required' => 0];
        if (count($normalizedRequiredValueKeys) > 0) {
            $existingValueKeys = array_keys($valueCollection);
            $completeness['complete'] = count(
                array_intersect($normalizedRequiredValueKeys, $existingValueKeys)
            );
            $completeness['required'] = count($normalizedRequiredValueKeys);
        }

        return $completeness;
    }

    private function getLabels(array $valueCollection, string $attributeAsLabel): array
    {
        return array_reduce(
            $valueCollection,
            function (array $labels, array $value) use ($attributeAsLabel) {
                if ($value['attribute'] === $attributeAsLabel) {
                    $labels[$value['locale']] = $value['data'];
                }

                return $labels;
            },
            []
        );
    }

    private function getImage(array $valueCollection, string $attributeAsImage): ?array
    {
        $emptyImage = null;

        $value = current(array_filter(
            $valueCollection,
            function (array $value) use ($attributeAsImage) {
                return $value['attribute'] === $attributeAsImage;
            }
        ));

        if (false === $value) {
            return $emptyImage;
        }

        return $value['data'];
    }
}
