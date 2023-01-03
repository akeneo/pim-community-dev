<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Category\Application\Converter\Checker;

use Akeneo\Category\Application\Converter\Checker\RequirementChecker;
use Akeneo\Category\Domain\ValueObject\PermissionCollection;
use Akeneo\Category\Infrastructure\Exception\ArrayConversionException;
use Akeneo\Category\Infrastructure\Exception\StructureArrayConversionException;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PermissionRequirementChecker implements RequirementChecker
{
    private const APPLY_ON_CHILDREN = 'apply_on_children';

    /**
     * @param array<string, array<int>> $data
     *
     * @throws ArrayConversionException
     */
    public function check(array $data): void
    {
        if (empty($data)) {
            return;
        }

        self::assertPermissionsArrayStructure($data);
    }

    /**
     * @param array<string, array<int>> $permissions
     */
    private static function assertPermissionsArrayStructure(array $permissions): void
    {
        foreach ($permissions as $key => $value) {
            if ($key === self::APPLY_ON_CHILDREN) {
                unset($permissions[$key]);
            } else {
                try {
                    Assert::inArray($key, [
                        PermissionCollection::VIEW,
                        PermissionCollection::EDIT,
                        PermissionCollection::OWN,
                    ]);
                    Assert::nullOrIsArray($value);
                } catch (\InvalidArgumentException $exception) {
                    throw new StructureArrayConversionException(sprintf('No empty value is expected, provided empty value for %s', $key));
                }
            }
        }
    }
}
