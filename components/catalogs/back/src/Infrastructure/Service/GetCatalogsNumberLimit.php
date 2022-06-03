<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Service;

/**
 * The following class is introduced to ease testability of classes that might require it.
 * As the set limit could be relatively high, without it integration tests would create a great amount
 * of catalogs, dampening tests performance
 *
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCatalogsNumberLimit
{
    public function __construct(private int $catalogsNumberLimit)
    {
    }

    public function getLimit(): int
    {
        return $this->catalogsNumberLimit;
    }

    public function setLimit(int $limit): void
    {
        $this->catalogsNumberLimit = $limit;
    }
}
