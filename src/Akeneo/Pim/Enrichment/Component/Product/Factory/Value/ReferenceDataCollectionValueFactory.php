<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Webmozart\Assert\Assert;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ReferenceDataCollectionValueFactory implements ValueFactory
{
    public function createWithoutCheckingData(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        $data = array_unique($data);
        $attributeCode = $attribute->code();

        if ($attribute->isLocalizableAndScopable()) {
            return ReferenceDataCollectionValue::scopableLocalizableValue($attributeCode, $data, $channelCode, $localeCode);
        }

        if ($attribute->isScopable()) {
            return ReferenceDataCollectionValue::scopableValue($attributeCode, $data, $channelCode);
        }

        if ($attribute->isLocalizable()) {
            return ReferenceDataCollectionValue::localizableValue($attributeCode, $data, $localeCode);
        }

        return ReferenceDataCollectionValue::value($attributeCode, $data);
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

        try {
            Assert::allString($data);
        } catch (\Exception $exception) {
            throw InvalidPropertyTypeException::validArrayStructureExpected(
                $attribute->code(),
                'one of the reference data code is not a string',
                static::class,
                $data
            );
        }

        $data = array_unique($data);
        sort($data);

        return $this->createWithoutCheckingData($attribute, $channelCode, $localeCode, $data);
    }

    public function supportedAttributeType(): string
    {
        return AttributeTypes::REFERENCE_DATA_MULTI_SELECT;
    }
}
