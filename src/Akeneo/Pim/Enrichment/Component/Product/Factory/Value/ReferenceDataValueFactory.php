<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ReferenceDataValueFactory implements ValueFactory
{
    public function createWithoutCheckingData(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        $attributeCode = $attribute->code();

        if ($attribute->isLocalizableAndScopable()) {
            return ReferenceDataValue::scopableLocalizableValue($attributeCode, $data, $channelCode, $localeCode);
        }

        if ($attribute->isScopable()) {
            return ReferenceDataValue::scopableValue($attributeCode, $data, $channelCode);
        }

        if ($attribute->isLocalizable()) {
            return ReferenceDataValue::localizableValue($attributeCode, $data, $localeCode);
        }

        return ReferenceDataValue::value($attributeCode, $data);
    }

    public function createByCheckingData(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        if (!is_string($data)) {
            throw InvalidPropertyTypeException::stringExpected(
                $attribute->code(),
                static::class,
                $data
            );
        }

        return $this->createWithoutCheckingData($attribute, $channelCode, $localeCode, $data);
    }

    public function supportedAttributeType(): string
    {
        return AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT;
    }
}
