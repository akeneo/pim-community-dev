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
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MetricValueFactory implements ValueFactory
{
    /** @var MetricFactory */
    private $metricFactory;

    public function __construct(MetricFactory $metricFactory)
    {
        $this->metricFactory = $metricFactory;
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
        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected(
                $attribute->code(),
                static::class,
                $data
            );
        }

        if (!isset($data['amount'])) {
            throw InvalidPropertyTypeException::arrayKeyExpected(
                $attribute->code(),
                'amount',
                static::class,
                $data
            );
        }

        if (!isset($data['unit'])) {
            throw InvalidPropertyTypeException::arrayKeyExpected(
                $attribute->code(),
                'unit',
                static::class,
                $data
            );
        }

        return $this->createWithoutCheckingData($attribute, $channelCode, $localeCode, $data);
    }

    public function supportedAttributeType(): string
    {
        return AttributeTypes::METRIC;
    }
}
