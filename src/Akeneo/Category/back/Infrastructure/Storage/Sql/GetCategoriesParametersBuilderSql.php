<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Handler\SearchFilters;
use Akeneo\Category\Application\Query\GetCategoriesParametersBuilder;
use Akeneo\Category\Infrastructure\DTO\ExternalApiSqlParameters;


/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoriesParametersBuilderSql implements GetCategoriesParametersBuilder
{
    public function __construct(
        private readonly SearchFilters $searchFilters,
    ) {
    }

    public function build(
        array $searchFilters,
        int $limit,
        int $offset,
        bool $isEnrichedAttributes,
    ): ExternalApiSqlParameters {
        if (empty($searchFilters)){
            $parameters = new ExternalApiSqlParameters('1=1', null, null);
        } else {
            $searchFiltersParameters = $this->searchFilters->build($searchFilters);
            $parameters = new ExternalApiSqlParameters(
                $searchFiltersParameters['sqlWhere'],
                $searchFiltersParameters['sqlParameters'],
                $searchFiltersParameters['sqlTypes']
            );
        }
        $parameters['sqlLimitOffset'] = $this->buildLimitOffset($limit, $offset);
        $parameters['params'] = [
            'with_enriched_attributes' => $isEnrichedAttributes ?: false,
        ];
        $parameters['types'] = [
            'with_enriched_attributes' => \PDO::PARAM_BOOL,
        ];

        return $parameters;
    }

    private function buildLimitOffset(int $limit, int $offset): string
    {
        $sqlLimitAndOffset = sprintf('LIMIT :limit', $limit);
        if ($offset !== 0) {
            $sqlLimitAndOffset .= sprintf(' OFFSET :offset', $offset);
        }

        return $sqlLimitAndOffset;
    }
}
