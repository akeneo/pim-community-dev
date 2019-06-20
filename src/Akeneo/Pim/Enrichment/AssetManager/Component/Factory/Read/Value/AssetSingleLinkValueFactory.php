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
use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetSingleLinkType;
use Akeneo\Pim\Enrichment\AssetManager\Component\Value\AssetSingleLinkValue;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value\ReadValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class AssetSingleLinkValueFactory implements ReadValueFactory
{
    public function create(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        $attributeCode = $attribute->code();
        $data = AssetCode::fromString($data);

        if ($attribute->isLocalizableAndScopable()) {
            return AssetSingleLinkValue::scopableLocalizableValue($attributeCode, $data, $channelCode, $localeCode);
        }

        if ($attribute->isLocalizable()) {
            return AssetSingleLinkValue::localizableValue($attributeCode, $data, $localeCode);
        }

        if ($attribute->isScopable()) {
            return AssetSingleLinkValue::scopableValue($attributeCode, $data, $channelCode);
        }

        return AssetSingleLinkValue::value($attributeCode, $data);
    }

    public function supportedAttributeType(): string
    {
        return AssetSingleLinkType::ASSET_SINGLE_LINK;
    }
}
