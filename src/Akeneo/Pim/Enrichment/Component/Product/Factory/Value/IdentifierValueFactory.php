<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\IdentifierValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IdentifierValueFactory implements ValueFactory
{
    public function createWithoutCheckingData(
        Attribute $attribute,
        ?string $channelCode,
        ?string $localeCode,
        $data
    ): ValueInterface {
        $attributeCode = $attribute->code();

        if ($attribute->isScopable() || $attribute->isLocalizable()) {
            throw new \InvalidArgumentException('An identifier value cannot be scopable nor localizable');
        }

        return IdentifierValue::value($attributeCode, $attribute->isMainIdentifier(), $data);
    }

    public function createByCheckingData(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        if (!\is_string($data)) {
            throw InvalidPropertyTypeException::stringExpected(
                $attribute->code(),
                self::class,
                $data
            );
        }

        if ('' === \trim($data)) {
            throw InvalidPropertyException::valueNotEmptyExpected(
                $attribute->code(),
                self::class
            );
        }

        return $this->createWithoutCheckingData($attribute, $channelCode, $localeCode, $data);
    }

    public function supportedAttributeType(): string
    {
        return AttributeTypes::IDENTIFIER;
    }
}
