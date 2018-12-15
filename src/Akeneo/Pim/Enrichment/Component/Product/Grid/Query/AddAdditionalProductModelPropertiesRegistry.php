<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Grid\Query;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AddAdditionalProductModelPropertiesRegistry
{
    /** @var AddAdditionalProductModelProperties[] */
    private $queries;

    /**
     * @param iterable $queries
     */
    public function __construct(iterable $queries)
    {
        $this->queries = (function (AddAdditionalProductModelProperties ...$query) {
            return $query;
        })(...$queries);
    }

    public function add(FetchProductAndProductModelRowsParameters $queryParameters, array $rows): array
    {
        foreach ($this->queries as $query) {
            $rows = $query->add($queryParameters, $rows);
        }

        return $rows;
    }
}
