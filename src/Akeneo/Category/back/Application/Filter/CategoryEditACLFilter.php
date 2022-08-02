<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Filter;

/**
 * Filters Std Format according to ACL rules.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryEditACLFilter
{
    /**
     * @param array<string, mixed> $collection
     * @param string $type
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function filterCollection(array $collection, string $type, array $options = []): array
    {
        return $collection;
    }
}
