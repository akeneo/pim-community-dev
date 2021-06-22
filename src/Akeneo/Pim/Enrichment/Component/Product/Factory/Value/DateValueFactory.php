<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidAttributeValueTypeException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDateAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\DateValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DateValueFactory implements ValueFactory
{
    private const DATE_FORMAT = 'yyyy-mm-dd';

    public function createWithoutCheckingData(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        $date = new \DateTime($data);
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

    public function createByCheckingData(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        if (!\is_string($data)) {
            throw InvalidAttributeValueTypeException::stringExpected(
                $attribute->code(),
                static::class,
                $data
            );
        }

        try {
            $date = new \DateTime($data);

            if (!\preg_match('/^\d{4}-\d{2}-\d{2}/', $data)) {
                throw new InvalidDateAttributeException(
                    $attribute->code(),
                    self::DATE_FORMAT,
                    static::class
                );
            }
        } catch (\Exception $e) {
            throw new InvalidDateAttributeException(
                $attribute->code(),
                self::DATE_FORMAT,
                static::class
            );
        }

        return $this->createWithoutCheckingData($attribute, $channelCode, $localeCode, $data);
    }

    public function supportedAttributeType(): string
    {
        return AttributeTypes::DATE;
    }
}
