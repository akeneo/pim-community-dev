<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\ExtensionVisitorInterface;

interface DatagridInterface
{
    /**
     * Adds extension to extension list
     *
     * @param ExtensionVisitorInterface $extension
     *
     * @return $this
     */
    public function addExtension(ExtensionVisitorInterface $extension);

    public function getName();

    /**
     * Returns array of registered extensions
     *
     * @return ExtensionVisitorInterface[]
     */
    public function getExtensions();

    /**
     * Set grid datasource
     *
     * @param DatasourceInterface $source
     *
     * @return $this
     */
    public function setDatasource(DatasourceInterface $source);

    /**
     * Returns datasource object
     *
     * @return DatasourceInterface
     */
    public function getDatasource();

    /**
     * Converts datasource into the result array
     * return array (
     *    'results' => converted source
     *    ....      => some additional info added by extensions
     * )
     *
     * @return array
     */
    public function getData();
}
