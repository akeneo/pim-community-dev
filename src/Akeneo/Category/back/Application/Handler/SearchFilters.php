<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Handler;

use Akeneo\Category\Infrastructure\DTO\ExternalApiSqlParameters;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SearchFilters
{
    /**
     * @param array<string, mixed> $searchFilters
     * @return ExternalApiSqlParameters
     */
    public function build(array $searchFilters): ExternalApiSqlParameters;
}
