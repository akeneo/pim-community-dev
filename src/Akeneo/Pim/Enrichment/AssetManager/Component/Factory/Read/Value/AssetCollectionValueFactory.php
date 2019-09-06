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

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Factory\Read\Value;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Pim\Enrichment\AssetManager\Component\Value\AssetCollectionValue;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value\ReadValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class AssetCollectionValueFactory implements ReadValueFactory
{
    public function create(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        $attributeCode = $attribute->code();
        $assetCodes = array_map(function (string $assetCode): AssetCode {
            return AssetCode::fromString($assetCode);
        }, $data);

        if ($attribute->isLocalizableAndScopable()) {
            return AssetCollectionValue::scopableLocalizableValue($attributeCode, $assetCodes, $channelCode, $localeCode);
        }

        if ($attribute->isLocalizable()) {
            return AssetCollectionValue::localizableValue($attributeCode, $assetCodes, $localeCode);
        }

        if ($attribute->isScopable()) {
            return AssetCollectionValue::scopableValue($attributeCode, $assetCodes, $channelCode);
        }

        return AssetCollectionValue::value($attributeCode, $assetCodes);
    }

    public function supportedAttributeType(): string
    {
        return AssetCollectionType::ASSET_COLLECTION;
    }
}
