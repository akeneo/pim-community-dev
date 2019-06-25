<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\PropertyAccessibleAsset;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Query\Asset\PropertyAccessibleAsset;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\ValuesDecoder;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class PropertyAccessibleAssetHydrator
{
    public function hydrate(array $result, array $attributesIndexedByIdentifier): PropertyAccessibleAsset
    {
        $valueCollection = ValuesDecoder::decode($result['value_collection']);
        $valuesIndexedForTemplate = $this->indexValues($valueCollection, $attributesIndexedByIdentifier);

        return new PropertyAccessibleAsset(
            $result['code'],
            $valuesIndexedForTemplate
        );
    }

    private function indexValues(array $values, array $attributesIndexedByIdentifier): array
    {
        $indexedValues = [];
        foreach ($values as $value) {
            $attributeIdentifier = $value['attribute'];
            if (!isset($attributesIndexedByIdentifier[$attributeIdentifier])) {
                continue;
            }

            /** @var AbstractAttribute $attribute */
            $attribute = $attributesIndexedByIdentifier[$attributeIdentifier];

            $channelPart = null === $value['channel'] ? '' : sprintf('-%s', $value['channel']);
            $localePart = null === $value['locale'] ? '' : sprintf('-%s', $value['locale']);
            $key = sprintf('%s%s%s', (string) $attribute->getCode(), $channelPart, $localePart);

            $indexedValues[$key] = $value['data'];
        }

        return $indexedValues;
    }
}
