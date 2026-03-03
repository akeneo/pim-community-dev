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
 * @phpstan-import-type InternalApi from InternalApiToStd
 * @phpstan-import-type PropertyApi from InternalApiToStd
 * @phpstan-import-type AttributeValueApi from InternalApiToStd
 */
class InternalApiRequirementChecker implements RequirementChecker
{
    /**
     * @param FieldsRequirementChecker $fieldsChecker
     * @param AttributeApiRequirementChecker $attributeChecker
     */
    public function __construct(
        private RequirementChecker $fieldsChecker,
        private RequirementChecker $attributeChecker,
    ) {
    }

    /**
     * @param InternalApi $data
     *
     * @throws ArrayConversionException
     */
    public function check(array $data): void
    {
        $this->checkArrayStructure($data);
        $this->fieldsChecker->check($data['properties']);
        $this->attributeChecker->check($data['attributes']);
    }

    /**
     * @param array{
     *     properties: PropertyApi,
     *     attributes: array<string, AttributeValueApi>
     * } $data
     */
    public function checkArrayStructure(array $data): void
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
