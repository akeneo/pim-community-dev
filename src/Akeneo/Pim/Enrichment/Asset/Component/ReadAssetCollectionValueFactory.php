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

namespace Akeneo\Pim\Enrichment\Asset\Component;

use Akeneo\Asset\Bundle\AttributeType\AttributeTypes;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value\ReadValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValue;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class ReadAssetCollectionValueFactory implements ReadValueFactory
{
    public function create(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        $attributeCode = $attribute->code();

        if ($attribute->isLocalizableAndScopable()) {
            return ReferenceDataCollectionValue::scopableLocalizableValue($attributeCode, $data, $channelCode, $localeCode);
        }

        if ($attribute->isLocalizable()) {
            return ReferenceDataCollectionValue::localizableValue($attributeCode, $data, $localeCode);
        }

        if ($attribute->isScopable()) {
            return ReferenceDataCollectionValue::scopableValue($attributeCode, $data, $channelCode);
        }

        return ReferenceDataCollectionValue::value($attributeCode, $data);
    }

    public function supportedAttributeType(): string
    {
        return AttributeTypes::ASSETS_COLLECTION;
    }
}
