<?php
declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\Number;

use Akeneo\Catalogs\Application\Mapping\Measurement\MeasurementConverter;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\NumberValueExtractorInterface;
use Akeneo\Catalogs\Application\Persistence\Attribute\FindOneAttributeByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @phpstan-import-type Attribute from FindOneAttributeByCodeQueryInterface
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 */
final class NumberFromMetricAttributeValueExtractor implements NumberValueExtractorInterface
{
    public function __construct(
        readonly private MeasurementConverter        $measurementConverter,
        private FindOneAttributeByCodeQueryInterface $findOneAttributeByCodeQuery,
    ) {
    }

    /**
     * @param RawProduct $product
     */
    public function extract(array $product, string $code, ?string $locale, ?string $scope, ?array $parameters): float|int|null
    {
        $metricUnit = $parameters['unit'] ?? null;
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

    /**
     * @return Attribute|null
     */
    private function getAttributeByCode(string $attributeCode): array|null
    {
        return $this->findOneAttributeByCodeQuery->execute($attributeCode);
    }

    /**
     * @param Attribute|null $attribute
     * @param array<array-key, mixed> $metricValue
     */
    private function findMetricUnitValue(?array $attribute, string $unit, array $metricValue): float|int|null
    {
        if (empty($attribute['measurement_family'])) {
            return null;
        }

        if (!isset($metricValue['unit']) || !\is_string($metricValue['unit'])) {
            return null;
        }

        if (!isset($metricValue['amount']) || !\is_numeric($metricValue['amount'])) {
            return null;
        }

        try {
            $amount = $this->measurementConverter->convert($attribute['measurement_family'], $metricValue['unit'], $unit, $metricValue['amount']);
        } catch (\Exception) {
            return null;
        }

        $castInIntAmount = (int) $amount;
        if ($castInIntAmount == $amount) {
            return $castInIntAmount;
        }

        return $amount;
    }
}
