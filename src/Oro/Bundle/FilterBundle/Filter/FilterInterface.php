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
     *
     * @return void
     */
    public function init($name, array $params);

    /**
     * Returns filter frontend name
     *
     * @return string
     */
    public function getName();

    /**
     * Returns form for validation of current submitted filter data
     *
     * @return Form
     */
    public function getForm();

    /**
     * Returns metadata for frontend
     *
     * @return array
     */
    public function getMetadata();

    /**
     * Applies a filter restrictions to a data source
     *
     * @param FilterDatasourceAdapterInterface $ds
     * @param mixed        $data
     *
     * @return bool true if a filter successfully applied; otherwise, false.
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data);
}
