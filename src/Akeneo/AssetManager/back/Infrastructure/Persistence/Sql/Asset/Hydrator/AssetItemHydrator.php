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
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetItem\ValueHydratorInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\ValuesDecoder;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class AssetItemHydrator implements AssetItemHydratorInterface
{
    /** @var AbstractPlatform */
    private $platform;

    /** @var FindRequiredValueKeyCollectionForChannelAndLocalesInterface */
    private $findRequiredValueKeyCollectionForChannelAndLocales;

    /** @var FindAttributesIndexedByIdentifierInterface */
    private $findAttributesIndexedByIdentifier;

    /** @var ValueHydratorInterface */
    private $valueHydrator;

    public function __construct(
        Connection $connection,
        FindRequiredValueKeyCollectionForChannelAndLocalesInterface $findRequiredValueKeyCollectionForChannelAndLocales,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        ValueHydratorInterface $valueHydrator
    ) {
        $this->platform = $connection->getDatabasePlatform();
        $this->findRequiredValueKeyCollectionForChannelAndLocales = $findRequiredValueKeyCollectionForChannelAndLocales;
        $this->findAttributesIndexedByIdentifier = $findAttributesIndexedByIdentifier;
        $this->valueHydrator = $valueHydrator;
    }

    public function hydrate(array $row, AssetQuery $query, $context = []): AssetItem
    {
        $identifier = Type::getType(Type::STRING)->convertToPHPValue($row['identifier'], $this->platform);
        $assetFamilyIdentifier = Type::getType(Type::STRING)->convertToPHPValue($row['asset_family_identifier'], $this->platform);
        $code = Type::getType(Type::STRING)->convertToPHPValue($row['code'], $this->platform);

        $indexedAttributes = $this->findAttributesIndexedByIdentifier->find(AssetFamilyIdentifier::fromString($assetFamilyIdentifier));
        $valueCollection = ValuesDecoder::decode($row['value_collection']);
        $valueCollection = $this->hydrateValues($valueCollection, $indexedAttributes, $context);

        $attributeAsLabel = Type::getType(Type::STRING)->convertToPHPValue($row['attribute_as_label'], $this->platform);
        $labels = $this->getLabels($valueCollection, $attributeAsLabel);
        $attributeAsImage = Type::getType(Type::STRING)->convertToPHPValue($row['attribute_as_image'], $this->platform);
        $image = $this->getImage($valueCollection, $attributeAsImage);

        $assetItem = new AssetItem();
        $assetItem->identifier = $identifier;
        $assetItem->assetFamilyIdentifier = $assetFamilyIdentifier;
        $assetItem->code = $code;
        $assetItem->labels = $labels;
        $assetItem->image = $image;
        $assetItem->values = $valueCollection;
        $assetItem->completeness = $this->getCompleteness($query, $valueCollection);

        return $assetItem;
    }

    private function getCompleteness(AssetQuery $query, $valueCollection): array
    {
        $normalizedRequiredValueKeys = $this->getRequiredValueKeys($query)->normalize();

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

    private function getRequiredValueKeys(AssetQuery $query): ValueKeyCollection
    {
        $assetFamilyFilter = $query->getFilter('asset_family');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($assetFamilyFilter['value']);
        $channelIdentifier = ChannelIdentifier::fromCode($query->getChannel());
        $localeIdentifiers = LocaleIdentifierCollection::fromNormalized([$query->getLocale()]);

        /** @var ValueKeyCollection $result */
        $result = $this->findRequiredValueKeyCollectionForChannelAndLocales->find(
            $assetFamilyIdentifier,
            $channelIdentifier,
            $localeIdentifiers
        );

        return $result;
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

    /**
     * Hydrate given $valueCollection according to given $indexedAttributes
     * using the value hydrator registry.
     */
    private function hydrateValues(array $valueCollection, array $indexedAttributes, array $context): array
    {
        $hydratedValueCollection = [];

        foreach ($valueCollection as $valueKey => $normalizedValue) {
            $attributeIdentifier = $normalizedValue['attribute'];
            if (!key_exists($attributeIdentifier, $indexedAttributes)) {
                continue;
            }

            $attribute = $indexedAttributes[$attributeIdentifier];
            $hydratedValueCollection[$valueKey] = $this->valueHydrator->hydrate($normalizedValue, $attribute, $context);
        }

        return $hydratedValueCollection;
    }
}
