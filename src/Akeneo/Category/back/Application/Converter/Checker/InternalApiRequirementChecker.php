<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Converter\Checker;

use Akeneo\Category\Infrastructure\Converter\InternalApi\InternalApiToStd;
use Akeneo\Category\Infrastructure\Exception\ArrayConversionException;
use Akeneo\Category\Infrastructure\Exception\StructureArrayConversionException;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * @phpstan-import-type PropertyApi from InternalApiToStd
 * @phpstan-import-type AttributeCodeApi from InternalApiToStd
 * @phpstan-import-type AttributeValueApi from InternalApiToStd
 */
class InternalApiRequirementChecker implements Requirement
{
    /**
     * @param array{
     *     properties: PropertyApi,
     *     attributes: array<string, AttributeCodeApi|AttributeValueApi>
     * } $data
     *
     * @throws ArrayConversionException
     */
    public function check(array $data): void
    {
        $expectedKeys = ['properties', 'attributes'];
        try {
            Assert::keyExists($data, 'properties');
            Assert::keyExists($data, 'attributes');
        } catch (\InvalidArgumentException $exception) {
            throw new StructureArrayConversionException(vsprintf('Fields ["%s", "%s"] is expected', $expectedKeys));
        }
    }
}
