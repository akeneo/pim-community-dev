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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifierCollection;
use Akeneo\AssetManager\Domain\Query\Asset\AssetItem;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\FindRequiredValueKeyCollectionForChannelAndLocalesInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetItem\ImagePreviewUrlGenerator;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetItem\ValueHydratorInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\ValuesDecoder;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class AssetItemHydrator implements AssetItemHydratorInterface
{
    public const THUMBNAIL_PREVIEW_TYPE = 'thumbnail';

    public function __construct(
        private Connection $connection,
        private FindRequiredValueKeyCollectionForChannelAndLocalesInterface $findRequiredValueKeyCollectionForChannelAndLocales,
        private FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        private ValueHydratorInterface $valueHydrator,
        private ImagePreviewUrlGenerator $imagePreviewUrlGenerator
    ) {
    }

    public function hydrate(array $row, AssetQuery $query, array $context = []): AssetItem
    {
        $platform = $this->connection->getDatabasePlatform();

        $identifier = Type::getType(Types::STRING)->convertToPHPValue($row['identifier'], $platform);
        $assetFamilyIdentifier = Type::getType(Types::STRING)->convertToPHPValue(
            $row['asset_family_identifier'],
            $platform
        );
        $code = Type::getType(Types::STRING)->convertToPHPValue($row['code'], $platform);

        $indexedAttributes = $this->findAttributesIndexedByIdentifier->find(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier)
        );
        $valueCollection = ValuesDecoder::decode($row['value_collection']);
        $valueCollection = $this->hydrateValues($valueCollection, $indexedAttributes, $context);

        $attributeAsLabel = Type::getType(Types::STRING)->convertToPHPValue($row['attribute_as_label'], $platform);
        $labels = $this->getLabels($valueCollection, $attributeAsLabel);
        $attributeAsMainMediaIdentifier = Type::getType(Types::STRING)->convertToPHPValue($row['attribute_as_main_media'], $platform);
        $images = $this->getImages($query, $valueCollection, $attributeAsMainMediaIdentifier);

        $assetItem = new AssetItem();
        $assetItem->identifier = $identifier;
        $assetItem->assetFamilyIdentifier = $assetFamilyIdentifier;
        $assetItem->code = $code;
        $assetItem->labels = $labels;
        $assetItem->image = $images;
        $assetItem->values = $valueCollection;
        $assetItem->completeness = $this->getCompleteness($query, $valueCollection);

        return $assetItem;
    }

    private function getCompleteness(AssetQuery $query, $valueCollection): array
    {
        $normalizedRequiredValueKeys = $this->getRequiredValueKeys($query)->normalize();

        $completeness = ['complete' => 0, 'required' => 0];
        if (!empty($normalizedRequiredValueKeys)) {
            $existingValueKeys = array_keys($valueCollection);
            $completeness['complete'] = count(
                array_intersect($normalizedRequiredValueKeys, $existingValueKeys)
            );
            $completeness['required'] = count($normalizedRequiredValueKeys);
        }

        return $completeness;
    }

    private function getRequiredValueKeys(AssetQuery $query): ValueKeyCollection
    {
        $assetFamilyFilter = $query->getFilter('asset_family');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($assetFamilyFilter['value']);
        $channelIdentifier = ChannelIdentifier::fromCode($query->getChannel());
        $localeIdentifiers = LocaleIdentifierCollection::fromNormalized([$query->getLocale()]);

        return $this->findRequiredValueKeyCollectionForChannelAndLocales->find(
            $assetFamilyIdentifier,
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

    private function getImages(AssetQuery $query, array $valueCollection, string $attributeAsMainMediaIdentifier): array
    {
        return array_values(array_filter(
            $valueCollection,
            fn (array $value) => $value['attribute'] === $attributeAsMainMediaIdentifier
        ));
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
