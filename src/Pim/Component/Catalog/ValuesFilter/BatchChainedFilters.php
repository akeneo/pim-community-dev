<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\ValuesFilter;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BatchChainedFilters implements BatchStorageFormatFilter
{
    /** @var BatchStorageFormatFilter[] */
    private $filters;

    /**
     * @param BatchStorageFormatFilter[] $filters
     */
    public function __construct(iterable $filters)
    {
        $this->filters = $filters;
    }

    /**
     * {@inheritdoc}
     */
    public function filterMany(array $rawValuesList): array
    {
        foreach ($this->filters as $filter) {
            $rawValuesList = $filter->filterMany($rawValuesList);
        }

        return $rawValuesList;
    }
}
