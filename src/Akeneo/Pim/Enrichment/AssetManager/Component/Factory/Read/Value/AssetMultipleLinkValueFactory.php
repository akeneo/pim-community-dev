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
use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetMultipleLinkType;
use Akeneo\Pim\Enrichment\AssetManager\Component\Value\AssetMultipleLinkValue;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class AssetMultipleLinkValueFactory implements ValueFactory
{
    public function createWithoutCheckingData(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        $attributeCode = $attribute->code();
        $assetCodes = array_map(function (string $assetCode): AssetCode {
            return AssetCode::fromString($assetCode);
        }, $data);

        if ($attribute->isLocalizableAndScopable()) {
            return AssetMultipleLinkValue::scopableLocalizableValue($attributeCode, $assetCodes, $channelCode, $localeCode);
        }

        if ($attribute->isLocalizable()) {
            return AssetMultipleLinkValue::localizableValue($attributeCode, $assetCodes, $localeCode);
        }

        if ($attribute->isScopable()) {
            return AssetMultipleLinkValue::scopableValue($attributeCode, $assetCodes, $channelCode);
        }

        return AssetMultipleLinkValue::value($attributeCode, $assetCodes);
    }

    public function createByCheckingData(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        if (null === $data) {
            return $this->createWithoutCheckingData($attribute, $channelCode, $localeCode, []);
        }

        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected(
                $attribute->code(),
                static::class,
                $data
            );
        }

        foreach ($data as $key => $value) {
            if (!is_string($value)) {
                throw InvalidPropertyTypeException::validArrayStructureExpected(
                    $attribute->code(),
                    sprintf('array key "%s" expects a string as value, "%s" given', $key, gettype($value)),
                    static::class,
                    $data
                );
            }
        }

        return $this->createWithoutCheckingData($attribute, $channelCode, $localeCode, $data);
    }

    public function supportedAttributeType(): string
    {
        return AssetMultipleLinkType::ASSET_MULTIPLE_LINK;
    }
}
