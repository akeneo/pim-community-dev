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

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Factory\Read\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityCollectionValue;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class ReferenceEntityCollectionValueFactory implements ValueFactory
{
    public function createWithoutCheckingData(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        $attributeCode = $attribute->code();
        $recordCodes = array_map(function (string $recordCode): RecordCode {
            return RecordCode::fromString($recordCode);
        }, $data ?: []);

        if ($attribute->isLocalizableAndScopable()) {
            return ReferenceEntityCollectionValue::scopableLocalizableValue($attributeCode, $recordCodes, $channelCode, $localeCode);
        }

        if ($attribute->isLocalizable()) {
            return ReferenceEntityCollectionValue::localizableValue($attributeCode, $recordCodes, $localeCode);
        }

        if ($attribute->isScopable()) {
            return ReferenceEntityCollectionValue::scopableValue($attributeCode, $recordCodes, $channelCode);
        }

        return ReferenceEntityCollectionValue::value($attributeCode, $recordCodes);
    }

    public function createByCheckingData(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
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

            try {
                RecordCode::fromString($value);
            } catch (\InvalidArgumentException $e) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    $attribute->code(),
                    'code',
                    $e->getMessage(),
                    static::class,
                    $value
                );
            }
        }

        return $this->createWithoutCheckingData($attribute, $channelCode, $localeCode, $data);
    }

    public function supportedAttributeType(): string
    {
        return ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION;
    }
}
