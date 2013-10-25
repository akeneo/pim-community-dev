<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Extension\Acceptor;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\ExtensionVisitorInterface;

interface DatagridInterface
{
    const METADATA_OPTIONS_KEY = 'options';

    /**
     * Returns datagrid name
     *
     * @return string
     */
    public function getName();

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
     * Returns datasource object accepted by extensions
     *
     * @return DatasourceInterface
     */
    public function getAcceptedDatasource();

    /**
     * Getter for acceptor object
     *
     * @return Acceptor
     */
    public function getAcceptor();

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

    /**
     * Retrieve metadata from all extensions
     * Metadata needed to create view layer
     *
     * @return \stdClass
     */
    public function getMetadata();
}
