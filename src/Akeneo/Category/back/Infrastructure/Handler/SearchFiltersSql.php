<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Handler;

use Akeneo\Category\Application\Handler\SearchFilters;
use Akeneo\Category\Application\Query\ExternalApiSqlParameters;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Infrastructure\Validation\ExternalApiSearchFiltersValidator;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchFiltersSql implements SearchFilters
{
    public function __construct(
        private readonly ExternalApiSearchFiltersValidator $searchFiltersValidator,
        private readonly GetCategoryInterface $getCategory,
    ) {
    }

    public function build(array $searchFilters): ExternalApiSqlParameters
    {
        $sqlWhere = '';
        $sqlParameters = [];
        $sqlTypes = [];
        $this->searchFiltersValidator->validate($searchFilters);
        foreach ($searchFilters as $field => $searchFilter) {
            foreach ($searchFilter as $key => $criterion) {
                $SqlParameter = sprintf('%s_%s', $field, $key);
                $SqlColumn = sprintf('category.%s', $field);
                switch ($criterion['operator']) {
                    case '=':
                        if ('parent' === $field) {
                            $parentCategory = $this->getCategory->byCode($criterion['value']);
                            if (!$parentCategory) {
                                throw new \InvalidArgumentException(sprintf('Parent code %s does not exist.', $criterion['value']));
                            }
                            $sqlWhere = $this->addSqlAndIfNecessary($sqlWhere);
                            $sqlWhere .= 'category.lft > :left AND category.rgt < :right AND category.root = :root';

                            $sqlParameters['left'] = $parentCategory->getPosition()->left;
                            $sqlParameters['right'] = $parentCategory->getPosition()->right;
                            $sqlParameters['root'] = $parentCategory->getRootId()->getValue();

                            $sqlTypes['left'] = \PDO::PARAM_INT;
                            $sqlTypes['right'] = \PDO::PARAM_INT;
                            $sqlTypes['root'] = \PDO::PARAM_INT;
                        } elseif ('is_root' === $field) {
                            $sqlWhere = $this->addSqlAndIfNecessary($sqlWhere);
                            if (true === (bool) $criterion['value']) {
                                $sqlWhere .= 'category.parent_id IS NULL';
                            } else {
                                $sqlWhere .= 'category.parent_id IS NOT NULL';
                            }
                        }
                        break;
                    case 'IN':
                        $sqlWhere = $this->addSqlAndIfNecessary($sqlWhere);
                        $sqlWhere .= "$SqlColumn IN (:$SqlParameter)";
                        $sqlParameters[$SqlParameter] = $criterion['value'];
                        $sqlTypes[$SqlParameter] = Connection::PARAM_STR_ARRAY;
                        break;
                    case '>':
                        $sqlWhere = $this->addSqlAndIfNecessary($sqlWhere);
                        $sqlWhere .= "$SqlColumn > :$SqlParameter";
                        $sqlParameters[$SqlParameter] = $criterion['value'];
                        $sqlTypes[$SqlParameter] = \PDO::PARAM_STR;
                        break;
                    default:
                        throw new \InvalidArgumentException('Invalid operator for search query.');
                }
            }
        }

        return new ExternalApiSqlParameters(
            $sqlWhere,
            $sqlParameters,
            $sqlTypes,
        );
    }

    private function addSqlAndIfNecessary(string $sqlWhere): string
    {
        if (!empty($sqlWhere)) {
            return $sqlWhere.' AND ';
        }

        return $sqlWhere;
    }
}
