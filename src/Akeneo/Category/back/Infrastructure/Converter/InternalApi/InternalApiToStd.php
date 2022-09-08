<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Converter\InternalApi;

use Akeneo\Category\Application\Converter\Checker\AttributeRequirementChecker;
use Akeneo\Category\Application\Converter\Checker\FieldsRequirementChecker;
use Akeneo\Category\Application\Converter\Checker\InternalApiRequirementChecker;
use Akeneo\Category\Application\Converter\ConverterInterface;
use Akeneo\Category\Infrastructure\Exception\ArrayConversionException;

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
        private AttributeRequirementChecker $attributeChecker,
        private InternalApiRequirementChecker $checker
    ) {
    }

    /**
     * @param array{
     *     properties: PropertyApi,
     *     attributes: array<string, AttributeCodeApi|AttributeValueApi>
     * } $data
     *
     * @retrun array|StandardInternalApi
     *
     * @throws ArrayConversionException
     */
    public function convert(array $data): array
    {
        // Validate the internal Api data and structure
        $this->checker->check($data);
        $this->fieldsChecker->check($data['properties']);
        $this->attributeChecker->check($data['attributes']);

        // Normalize
        $convertedData = [];
        $convertedData['code'] = $data['properties']['code'];
        $convertedData['labels'] = $data['properties']['labels'];
        $convertedData['values'] = $data['attributes'];

        return $convertedData;
    }
}
