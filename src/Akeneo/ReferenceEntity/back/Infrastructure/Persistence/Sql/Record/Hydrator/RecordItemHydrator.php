<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindRequiredValueKeyCollectionForChannelAndLocalesInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordItem;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordItem\ValueHydratorInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\ValuesDecoder;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class RecordItemHydrator implements RecordItemHydratorInterface
{
    public function __construct(
        private Connection $connection,
        private FindRequiredValueKeyCollectionForChannelAndLocalesInterface $findRequiredValueKeyCollectionForChannelAndLocales,
        private FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        private ValueHydratorInterface $valueHydrator
    ) {
    }

    public function hydrate(array $row, RecordQuery $query, $context = []): RecordItem
    {
        $platform = $this->connection->getDatabasePlatform();
        $identifier = Type::getType(Types::STRING)->convertToPHPValue($row['identifier'], $platform);
        $referenceEntityIdentifier = Type::getType(Types::STRING)->convertToPHPValue($row['reference_entity_identifier'], $platform);
        $code = Type::getType(Types::STRING)->convertToPHPValue($row['code'], $platform);

        $indexedAttributes = $this->findAttributesIndexedByIdentifier->find(ReferenceEntityIdentifier::fromString($referenceEntityIdentifier));
        $valueCollection = ValuesDecoder::decode($row['value_collection']);
        $valueCollection = $this->hydrateValues($valueCollection, $indexedAttributes, $context);

        $attributeAsLabel = Type::getType(Types::STRING)->convertToPHPValue($row['attribute_as_label'], $platform);
        $labels = $this->getLabels($valueCollection, $attributeAsLabel);
        $attributeAsImage = Type::getType(Types::STRING)->convertToPHPValue($row['attribute_as_image'], $platform);
        $image = $this->getImage($valueCollection, $attributeAsImage);

        $recordItem = new RecordItem();
        $recordItem->identifier = $identifier;
        $recordItem->referenceEntityIdentifier = $referenceEntityIdentifier;
        $recordItem->code = $code;
        $recordItem->labels = $labels;
        $recordItem->image = $image;
        $recordItem->values = $valueCollection;
        $recordItem->completeness = $this->getCompleteness($query, $valueCollection);

        return $recordItem;
    }

    private function getCompleteness(RecordQuery $query, $valueCollection): array
    {
        $normalizedRequiredValueKeys = $this->getRequiredValueKeys($query)->normalize();

        $completeness = ['complete' => 0, 'required' => 0];
        if ([] !== $normalizedRequiredValueKeys) {
            $existingValueKeys = array_keys($valueCollection);
            $completeness['complete'] = count(
                array_intersect($normalizedRequiredValueKeys, $existingValueKeys)
            );
            $completeness['required'] = count($normalizedRequiredValueKeys);
        }

        return $completeness;
    }

    private function getRequiredValueKeys(RecordQuery $query): ValueKeyCollection
    {
        $referenceEntityFilter = $query->getFilter('reference_entity');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityFilter['value']);
        $channelIdentifier = ChannelIdentifier::fromCode($query->getChannel());
        $localeIdentifiers = LocaleIdentifierCollection::fromNormalized([$query->getLocale()]);

        return $this->findRequiredValueKeyCollectionForChannelAndLocales->find(
            $referenceEntityIdentifier,
            $channelIdentifier,
            $localeIdentifiers
        );
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
        $value = current(array_filter(
            $valueCollection,
            static fn (array $value) => $value['attribute'] === $attributeAsImage
        ));

        if (false === $value) {
            return null;
        }

        return $value['data'];
    }

    /**
     * Hydrate given $valueCollection according to given $indexedAttributes
     * using the value hydrator registry.
     */
    private function hydrateValues(array $valueCollection, array $indexedAttributes, array $context): array
    {
        $hydratedValueCollection = [];

        foreach ($valueCollection as $valueKey => $normalizedValue) {
            $attributeIdentifier = $normalizedValue['attribute'];
            if (!array_key_exists($attributeIdentifier, $indexedAttributes)) {
                continue;
            }

            $attribute = $indexedAttributes[$attributeIdentifier];
            $hydratedValueCollection[$valueKey] = $this->valueHydrator->hydrate($normalizedValue, $attribute, $context);
        }

        return $hydratedValueCollection;
    }
}
