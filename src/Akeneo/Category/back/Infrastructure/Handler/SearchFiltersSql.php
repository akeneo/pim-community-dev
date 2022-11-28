<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Handler;

use Akeneo\Category\Application\Handler\SearchFilters;
use Akeneo\Category\Infrastructure\Validation\ExternalApiSearchFiltersValidator;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchFiltersSql implements SearchFilters
{
    public function __construct(
        private readonly ExternalApiSearchFiltersValidator $searchFiltersValidator
    ) {
    }

    public function build(array $searchFilters): string
    {
        $this->searchFiltersValidator->validate($searchFilters);
        //TODO: build search filters
    }
}
