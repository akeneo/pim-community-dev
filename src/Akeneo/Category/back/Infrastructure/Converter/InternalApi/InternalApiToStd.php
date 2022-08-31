<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Converter\InternalApi;

use Akeneo\Category\Application\Converter\AttributeRequirementChecker;
use Akeneo\Category\Application\Converter\ConverterInterface;
use Akeneo\Category\Application\Converter\FieldsRequirementChecker;
use Akeneo\Category\Infrastructure\Exception\ArrayConversionException;
use Akeneo\Category\Infrastructure\Exception\StructureArrayConversionException;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type InternalApi array{
 *     properties: PropertyApi,
 *     attributes: array<string, AttributeCodeApi|AttributeValueApi>
 * }
 * @phpstan-type StandardInternalApi array{
 *     code: string,
 *     labels: array<string, string>
 *     values: array<string, array<AttributeCodeApi|AttributeValueApi>>
 * }
 * @phpstan-type PropertyApi array{code: string, labels: array<string, string>}
 * @phpstan-type AttributeCodeApi array<string>
 * @phpstan-type AttributeValueApi array{data: string, locale: string|null, attribute_code: string}
 */
class InternalApiToStd implements ConverterInterface
{
    public function __construct(
        private FieldsRequirementChecker $fieldsChecker,
        private AttributeRequirementChecker $attributeChecker
    ) {
    }

    /**
     * @param array{
     *     properties: PropertyApi,
     *     attributes: array<string, AttributeCodeApi|AttributeValueApi>
     * } $data
     *
     * @retrun array|StandardInternalApi
     */
    public function convert(array $data): array
    {
<<<<<<< HEAD
        // Validate the internal Api data and structure
        $this->checkArrayStructure($data);
        $this->checkProperties($data['properties']);
        $this->checkAttributes($data['attributes']);
=======
        $this->fieldsRequirementChecker->checkFieldsExist($data, ['code', 'labels']);
        $this->fieldsRequirementChecker->checkFieldsNotEmpty($data, ['code']);
>>>>>>> 326ee84f91... GRF-223: Save labels

        // Normalize
        $convertedData = [];
        $convertedData['code'] = $data['properties']['code'];
        $convertedData['labels'] = $data['properties']['labels'];
        $convertedData['values'] = $data['attributes'];

        return $convertedData;
    }

    /**
     * @param array{
     *     properties: PropertyApi,
     *     attributes: array<string, AttributeCodeApi|AttributeValueApi>
     * } $data
     *
     * @throws ArrayConversionException
     */
    private function checkArrayStructure(array $data): void
    {
        $expectedKeys = ['properties', 'attributes'];
        try {
            Assert::keyExists($data, 'properties');
            Assert::keyExists($data, 'attributes');
        } catch (\InvalidArgumentException $exception) {
            throw new StructureArrayConversionException(
                vsprintf('Fields ["%s", "%s"] is expected', $expectedKeys)
            );
        }
    }

    /**
     * @param array{code: string, labels: array<string, string>} $properties
     *
     * @throws ArrayConversionException
     */
    private function checkProperties(array $properties): void
    {
        $this->fieldsChecker->checkFieldsExist($properties, ['code', 'labels']);
        $this->fieldsChecker->checkFieldsNotEmpty($properties, ['code']);
    }

    /**
     * @param array<string, AttributeCodeApi|AttributeValueApi> $attributes
     *
     * @throws ArrayConversionException
     */
    private function checkAttributes(array $attributes): void
    {
        $this->attributeChecker->checkKeyExist($attributes, 'attribute_codes');

        /** @var $attributeValues array<string, AttributeValueApi}> */
        $attributeValues = array_filter($attributes, function ($attributeKey) {
            return $attributeKey !== 'attribute_codes';
        }, ARRAY_FILTER_USE_KEY);

        $this->attributeChecker->checkAttributeValueKeysExist($attributeValues, $attributes['attribute_codes']);

        $this->attributeChecker->checkAttributeValueArrayStructure($attributeValues);
    }
}
