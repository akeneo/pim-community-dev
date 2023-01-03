<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Category\Application\Converter\Checker;

use Akeneo\Category\Application\Converter\Checker\InternalApiRequirementChecker;
use Akeneo\Category\Application\Converter\Checker\RequirementChecker;
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
class InternalApiPermissionRequirementChecker extends InternalApiRequirementChecker
{
    public function __construct(
        private readonly RequirementChecker $fieldsChecker,
        private readonly RequirementChecker $attributeChecker,
        private readonly RequirementChecker $permissionRequirementChecker,
    ) {
        parent::__construct($this->fieldsChecker, $this->attributeChecker);
    }

    /**
     * @param InternalApi $data
     *
     * @throws ArrayConversionException
     */
    public function check(array $data): void
    {
        parent::check($data);

        $this->checkPermissionsArrayStructure($data);
        $this->permissionRequirementChecker->check($data['permissions']);
    }

    /**
     * @param array{
     *     properties: PropertyApi,
     *     attributes: array<string, AttributeValueApi>,
     *     permissions: array<string, array<array{id: int, label:string}>>|null
     * } $data
     */
    public function checkPermissionsArrayStructure(array $data): void
    {
        try {
            Assert::keyExists($data, 'permissions');
        } catch (\InvalidArgumentException $exception) {
            throw new StructureArrayConversionException('Field ["permissions"] is expected');
        }
    }
}
