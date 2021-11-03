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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Value\ArrayConverter;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter\ValueConverterInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;

final class FlatToStandard implements ValueConverterInterface
{
    /**
     * @param array $attributeFieldInfo
     * @param string $value
     *
     * @return array
     * @throws DataArrayConversionException
     */
    public function convert(array $attributeFieldInfo, $value): array
    {
        if (\trim($value) === '') {
            $data = null;
        } else {
            try {
                $data = \json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw new DataArrayConversionException('Invalid json');
            }
        }

        return [
            $attributeFieldInfo['attribute']->getCode() => [
                [
                    'locale' => $attributeFieldInfo['locale_code'],
                    'scope' => $attributeFieldInfo['scope_code'],
                    'data' => $data,
                ],
            ],
        ];
    }

    public function supportsField($attributeType): bool
    {
        return AttributeTypes::TABLE === $attributeType;
    }
}
