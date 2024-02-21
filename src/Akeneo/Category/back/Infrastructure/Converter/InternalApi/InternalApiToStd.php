<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Converter\InternalApi;

use Akeneo\Category\Application\Converter\Checker\RequirementChecker;
use Akeneo\Category\Application\Converter\ConverterInterface;
use Akeneo\Category\Infrastructure\Exception\ArrayConversionException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type InternalApi array{
 *     properties: PropertyApi,
 *     attributes: array<string, AttributeValueApi>,
 *     permissions: array<string, array<array{id: int, label: string}>>|null
 * }
 * @phpstan-type StandardInternalApi array{
 *     code: string,
 *     labels: array<string, string>,
 *     values: array<string, array<AttributeValueApi>>,
 *     permissions: array<string, array<array{id: int, label: string}>>|null
 * }
 * @phpstan-type PropertyApi array{code: string, labels: array<string, string>}
 * @phpstan-type AttributeValueApi array{data: string, channel: string|null, locale: string|null, attribute_code: string}
 */
class InternalApiToStd implements ConverterInterface
{
    public function __construct(
        private readonly RequirementChecker $checker,
    ) {
    }

    /**
     * @param InternalApi $data
     *
     * @return StandardInternalApi
     *
     * @throws ArrayConversionException
     */
    public function convert(array $data): array
    {
        // Validate the internal Api data and structure
        $this->checker->check($data);

        // Normalize
        $convertedData = [];
        $convertedData['id'] = $data['id'];
        $convertedData['code'] = $data['properties']['code'];
        $convertedData['labels'] = $data['properties']['labels'];
        $convertedData['values'] = $data['attributes'];

        return $convertedData;
    }
}
