<?php

namespace Oro\Bundle\FilterBundle\Filter;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Symfony\Component\Form\Form;

interface FilterInterface
{
    /**
     * Initialize current filter by config
     *
     * @param string $name
     * @param array  $params
     */
    public function init(string $name, array $params): void;

    /**
     * Returns filter frontend name
     */
    public function getName(): string;

    /**
     * Returns form for validation of current submitted filter data
     */
    public function getForm(): Form;

    /**
     * Returns metadata for frontend
     */
    public function getMetadata(): array;

    /**
     * Applies a filter restrictions to a data source
     *
     * @param FilterDatasourceAdapterInterface $ds
     * @param mixed        $data
     *
     * @return bool true if a filter successfully applied; otherwise, false.
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data): bool;
}
