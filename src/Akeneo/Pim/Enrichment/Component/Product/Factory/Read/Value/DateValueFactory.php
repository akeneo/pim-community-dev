<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\DateValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DateValueFactory implements ReadValueFactory
{
    public function create(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        if (!is_string($data)) {
            throw InvalidPropertyTypeException::stringExpected(
                $attribute->code(),
                static::class,
                $data
            );
        }

        try {
            $date = new \DateTime($data);
        } catch (\Exception $ex) {
            throw InvalidPropertyException::dateExpected(
                $attribute->code(),
                'yyyy-mm-dd',
                static::class,
                $data
            );
        }

        $attributeCode = $attribute->code();

        if ($attribute->isLocalizableAndScopable()) {
            return DateValue::scopableLocalizableValue($attributeCode, $date, $channelCode, $localeCode);
        }

        if ($attribute->isScopable()) {
            return DateValue::scopableValue($attributeCode, $date, $channelCode);
        }

        if ($attribute->isLocalizable()) {
            return DateValue::localizableValue($attributeCode, $date, $localeCode);
        }

        return DateValue::value($attributeCode, $date);
    }

    public function supportedAttributeType(): string
    {
        return AttributeTypes::DATE;
    }
}
