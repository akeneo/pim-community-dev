<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\ValuesFilter;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChainedFilters implements StorageFormatFilter
{
    /** @var StorageFormatFilter[] */
    private $filters;

    /**
     * @param StorageFormatFilter[] $filters
     */
    public function __construct(iterable $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * {@inheritdoc}
     */
    public function filterSingle(array $rawValues): array
    {
        foreach ($this->filters as $filter) {
            $rawValues = $filter->filterSingle($rawValues);
        }

        return $rawValues;
    }
}
