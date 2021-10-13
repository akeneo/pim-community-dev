<?php

declare(strict_types=1);

namespace Akeneo\Pim\TableAttribute\Infrastructure\Value\Filter;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ColumnDefinition;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ColumnTypeFilter
{
    public function supportedColumnType(): string;

    public function supportsOperator(string $operator): bool;

    /**
     * @param mixed $value
     */
    public function addFilter(
        SearchQueryBuilder $searchQueryBuilder,
        string $attributeCode,
        string $operator,
        ColumnDefinition $columnDefinition,
        bool $isFirstColumn,
        ?string $rowCode,
        ?string $locale,
        ?string $channel,
        $value
    ): void;
}
