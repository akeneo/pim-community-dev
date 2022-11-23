<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetCategoriesParametersBuilder;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoriesParametersBuilderSql implements GetCategoriesParametersBuilder
{
    /**
     * @param array<string> $categoryCodes
     *
     * @return array<string, string>
     */
    public function build(
        array $categoryCodes,
        int $limit,
        int $offset,
        bool $isEnrichedAttributes,
    ): array {
        $parameters['sqlWhere'] = $this->buildSearchFilter($categoryCodes);
        $parameters['sqlLimitOffset'] = $this->buildLimitOffset($limit, $offset);
        $parameters['params'] = [
            'category_codes' => $categoryCodes,
            'with_enriched_attributes' => $isEnrichedAttributes ?: false,
        ];
        $parameters['types'] = [
            'category_codes' => Connection::PARAM_STR_ARRAY,
            'with_enriched_attributes' => \PDO::PARAM_BOOL,
        ];

        return $parameters;
    }

    // TODO: Will be replaced in https://akeneo.atlassian.net/browse/GRF-376
    private function buildSearchFilter(array $searchParameter): string
    {
        if (empty($searchParameter)) {
            $sqlWhere = '1=1';
        } else {
            $sqlWhere = 'category.code IN (:category_codes)';
        }

        return $sqlWhere;
    }

    private function buildLimitOffset(int $limit, int $offset): string
    {
        $sqlLimitAndOffset = sprintf('LIMIT %d', $limit);
        if ($offset !== 0) {
            $sqlLimitAndOffset .= sprintf(' OFFSET %d', $offset);
        }

        return $sqlLimitAndOffset;
    }
}
