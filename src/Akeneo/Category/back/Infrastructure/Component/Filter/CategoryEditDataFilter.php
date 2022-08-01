<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Component\Filter;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryEditDataFilter implements CollectionFilterInterface
{
    public function filterCollection($collection, $type, array $options = []): array
    {
        return $collection;
    }
}
