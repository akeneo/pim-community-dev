<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Handler\SearchFilters;
use Akeneo\Category\Application\Query\ExternalApiSqlParameters;
use Akeneo\Category\Application\Query\GetCategoriesParametersBuilder;

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
        bool $withPosition,
        bool $isEnrichedAttributes,
    ): ExternalApiSqlParameters {
        if (empty($searchFilters)) {
            $sqlParameters = new ExternalApiSqlParameters('1=1');
        } else {
            $sqlParameters = $this->searchFilters->build($searchFilters);
        }

        $sqlParameters = $this->buildLimitOffset($sqlParameters, $limit, $offset);
        $sqlParameters = $this->buildWithEnrichedAttributes($sqlParameters, $isEnrichedAttributes);
        $sqlParameters = $this->buildWithPosition($sqlParameters, $withPosition);

        return $sqlParameters;
    }

    private function buildLimitOffset(
        ExternalApiSqlParameters $sqlParameters,
        int $limit,
        int $offset,
    ): ExternalApiSqlParameters {
        $sqlParametersParams = $sqlParameters->getParams();
        $sqlParametersTypes = $sqlParameters->getTypes();

        $sqlLimitAndOffset = 'LIMIT :limit';
        $sqlParametersParams['limit'] = $limit;
        $sqlParametersTypes['limit'] = \PDO::PARAM_INT;
        if ($offset !== 0) {
            $sqlLimitAndOffset .= ' OFFSET :offset';
            $sqlParametersParams['offset'] = $offset;
            $sqlParametersTypes['offset'] = \PDO::PARAM_INT;
        }

        $sqlParameters
            ->setLimitAndOffset($sqlLimitAndOffset)
            ->setParams($sqlParametersParams)
            ->setTypes($sqlParametersTypes)
        ;

        return $sqlParameters;
    }

    private function buildWithEnrichedAttributes(
        ExternalApiSqlParameters $sqlParameters,
        bool $isEnrichedAttributes,
    ): ExternalApiSqlParameters {
        $sqlParametersParams = $sqlParameters->getParams();
        $sqlParametersTypes = $sqlParameters->getTypes();

        $sqlParametersParams['with_enriched_attributes'] = $isEnrichedAttributes;
        $sqlParametersTypes['with_enriched_attributes'] = \PDO::PARAM_BOOL;

        $sqlParameters
            ->setParams($sqlParametersParams)
            ->setTypes($sqlParametersTypes)
        ;

        return $sqlParameters;
    }

    private function buildWithPosition(
        ExternalApiSqlParameters $sqlParameters,
        bool $withPosition,
    ): ExternalApiSqlParameters {
        $sqlParametersParams = $sqlParameters->getParams();
        $sqlParametersTypes = $sqlParameters->getTypes();

        $sqlParametersParams['with_position'] = $withPosition;
        $sqlParametersTypes['with_position'] = \PDO::PARAM_BOOL;

        $sqlParameters
            ->setParams($sqlParametersParams)
            ->setTypes($sqlParametersTypes)
        ;

        return $sqlParameters;
    }
}
