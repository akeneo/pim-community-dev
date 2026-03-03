<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\MetricFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MetricValueFactory implements ValueFactory
{
    private const AMOUNT_DECIMAL_FORMAT_REGEX = '#^-?\d+(\.\d+)?$#';

    public function __construct(
        private readonly MetricFactory $metricFactory,
    ) {
    }

    public function createWithoutCheckingData(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        $data = $this->metricFactory->createMetric($attribute->metricFamily(), $data['unit'], $data['amount']);
        $attributeCode = $attribute->code();

        if ($attribute->isLocalizableAndScopable()) {
            return MetricValue::scopableLocalizableValue($attributeCode, $data, $channelCode, $localeCode);
        }

        if ($attribute->isScopable()) {
            return MetricValue::scopableValue($attributeCode, $data, $channelCode);
        }

        if ($attribute->isLocalizable()) {
            return MetricValue::localizableValue($attributeCode, $data, $localeCode);
        }

        return MetricValue::value($attributeCode, $data);
    }

    public function createByCheckingData(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        if (!\is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected(
                $attribute->code(),
                MetricValueFactory::class,
                $data
            );
        }

        if (!array_key_exists('amount', $data)) {
            throw InvalidPropertyTypeException::arrayKeyExpected(
                $attribute->code(),
                'amount',
                MetricValueFactory::class,
                $data
            );
        }

        if (!array_key_exists('unit', $data)) {
            throw InvalidPropertyTypeException::arrayKeyExpected(
                $attribute->code(),
                'unit',
                MetricValueFactory::class,
                $data
            );
        }

        if (!is_string($data['unit'])) {
            throw InvalidPropertyTypeException::validArrayStructureExpected(
                $attribute->code(),
                sprintf('key "unit" has to be a string, "%s" given', gettype($data['unit'])),
                MetricValueFactory::class,
                $data
            );
        }

        if (is_string($data['amount']) && is_numeric($data['amount']) && !preg_match(self::AMOUNT_DECIMAL_FORMAT_REGEX, $data['amount'])) {
            throw InvalidPropertyTypeException::decimalExpected(
                $attribute->code(),
                MetricValueFactory::class,
                $data['amount'],
            );
        }

        return $this->createWithoutCheckingData($attribute, $channelCode, $localeCode, $data);
    }

    public function supportedAttributeType(): string
    {
        return AttributeTypes::METRIC;
    }
}
