<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Versioning\Model;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ValueComparatorInterface
{
    /**
     * @return string[]
     */
    public function getSupportedResourceNames(): array;

    public function supportsField(string $field): bool;

    public function isEqual($value1, $value2): bool;
}
