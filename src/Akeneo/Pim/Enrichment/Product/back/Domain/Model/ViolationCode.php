<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\Model;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ViolationCode
{
    public const PERMISSION = 1;
    public const USER_IS_NOT_OWNER = 2;
    public const USER_CANNOT_EDIT_ATTRIBUTE = 4;

    public static function buildGlobalViolationCode(int ...$violationCodes): int
    {
        return \array_sum($violationCodes);
    }

    public static function containsViolationCode(int $globalViolationCode, int $violationCode): bool
    {
        return ($violationCode & $globalViolationCode) > 0;
    }
}
