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
        readonly private MeasurementConverter $measurementConverter,
        readonly private FindOneAttributeByCodeQueryInterface $findOneAttributeByCodeQuery,
    ) {
    }

    /**
     * @param RawProduct $product
     */
    public function extract(array $product, string $code, ?string $locale, ?string $scope, ?array $parameters): float|int|null
    {
        if (empty($product['raw_values'])) {
            return null;
        }
        $targetedUnit = $parameters['unit'] ?? null;

        $productValue = $product['raw_values'][$code][$scope][$locale] ?? null;

        if (!\is_string($targetedUnit) || !\is_array($productValue)) {
            return null;
        }

        $attribute = $this->getAttributeByCode($code);
        if (null === $attribute) {
            return null;
        }

        return $this->findMetricUnitValue($attribute, $targetedUnit, $productValue);
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
     * @param Attribute $attribute
     * @param array<array-key, mixed> $productMeasurementValue
     */
    private function findMetricUnitValue(array $attribute, string $targetedunit, array $productMeasurementValue): float|int|null
    {
        if (empty($attribute['measurement_family'])) {
            return null;
        }

        if (!isset($productMeasurementValue['unit']) || !\is_string($productMeasurementValue['unit'])) {
            return null;
        }

        if (!isset($productMeasurementValue['amount']) || !\is_numeric($productMeasurementValue['amount'])) {
            return null;
        }

        try {
            $amount = $this->measurementConverter->convert($attribute['measurement_family'], $targetedunit, $productMeasurementValue['unit'], $productMeasurementValue['amount']);
        } catch (\LogicException) {
            return null;
        }

        $castInIntAmount = (int) $amount;
        if ($castInIntAmount == $amount) {
            return $castInIntAmount;
        }

        return $amount;
    }
}
