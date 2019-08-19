<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class BooleanValueFactory extends ScalarValueFactory implements ValueFactory
{
    public function createWithoutCheckingData(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        return parent::createWithoutCheckingData($attribute, $channelCode, $localeCode, (bool) $data);
    }

    public function createByCheckingData(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        if (null === $data) {
            return $this->createWithoutCheckingData($attribute, $channelCode, $localeCode, $data);
        }

        $dataToPersist = $data;

        if (is_string($data) && ('1' === $data || '0' === $data)) {
            $dataToPersist = boolval($data);
        }

        if (is_string($data) && ('true' === $data || 'false' === $data)) {
            $dataToPersist = (bool) $data;
        }

        if (!is_bool($dataToPersist)) {
            throw InvalidPropertyTypeException::booleanExpected(
                $attribute->code(),
                static::class,
                $data
            );
        }

        return $this->createWithoutCheckingData($attribute, $channelCode, $localeCode, $data);
    }

    public function supportedAttributeType(): string
    {
        return AttributeTypes::BOOLEAN;
    }
}
