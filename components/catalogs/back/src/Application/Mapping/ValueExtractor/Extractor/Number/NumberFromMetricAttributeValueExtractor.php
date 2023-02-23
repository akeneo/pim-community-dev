<?php
 declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\Number;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\NumberValueExtractorInterface;
use Akeneo\Catalogs\Application\Persistence\Attribute\FindOneAttributeByCodeQueryInterface;
use Akeneo\Catalogs\Infrastructure\Measurement\MeasurementConverter;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class NumberFromMetricAttributeValueExtractor implements NumberValueExtractorInterface
{
    public function __construct(
        readonly private MeasurementConverter $measurementConverter,
        private FindOneAttributeByCodeQueryInterface $findOneAttributeByCodeQuery
    )
    {
    }

    public function extract(array $product, string $code, ?string $locale, ?string $scope, ?array $parameters): null|float|int
    {
        $metricUnit = $parameters['unit'] ?? null;
        /** @var mixed $value */
        $metricValue = $product['raw_values'][$code][$scope][$locale] ?? null;

        if (!\is_string($metricUnit) || !\is_array($metricValue)) {
            return null;
        }
        $attribute = $this->getAttributeByCode($code);
        return $this->findMetricUnitValue($attribute, $metricUnit, $metricValue);
    }

    public function getSupportedSourceType(): string
    {
        return self::SOURCE_TYPE_ATTRIBUTE_METRIC;
    }

    public function getSupportedTargetType(): string
    {
        return self::TARGET_TYPE_NUMBER;
    }

    public function getSupportedTargetFormat(): ?string
    {
        return null;
    }

    private function getAttributeByCode(string $attributeCode): array|null
    {
        $attribute = $this->findOneAttributeByCodeQuery->execute($attributeCode);
        if (null === $attribute) {
            return null;
        }
        return $attribute;
    }

    private function findMetricUnitValue(array $attribute, string $unit, array $metricValue): float|int|string|null
    {
        $amount = $this->measurementConverter->convert($attribute['measurement_family'], $metricValue['unit'], $unit, $metricValue['amount']);
        if (\is_numeric($amount)) {
            $castInIntAmount = (int) $amount;
            if ($castInIntAmount == $amount) {
                return $castInIntAmount;
            }
            return (float) $amount;
        }
        return null;
    }
}
