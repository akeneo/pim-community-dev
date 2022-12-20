<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Query;

use Akeneo\Category\ServiceApi\ExternalApiCategory;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetCategoriesInterface
{
    /**
     * @return array<ExternalApiCategory>
     */
    public function execute(ExternalApiSqlParameters $sqlParameters): array;

    public function count(ExternalApiSqlParameters $parameters): int|null;
}
