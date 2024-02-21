<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application\Category;

use Akeneo\Pim\Enrichment\Product\API\Query\GetProductCategoryCodesQuery;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductCategoryCodesHandler
{
    public function __construct(private readonly GetCategoryCodes $getCategoryCodes)
    {
    }

    /**
     * @return array<string, string[]>
     */
    public function __invoke(GetProductCategoryCodesQuery $query): array
    {
        return $this->getCategoryCodes->fromProductUuids($query->productUuids);
    }
}
