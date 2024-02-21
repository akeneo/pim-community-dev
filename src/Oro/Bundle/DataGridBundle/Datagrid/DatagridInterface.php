<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataIterableObject;
use Oro\Bundle\DataGridBundle\Datagrid\Common\ResultsIterableObject;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\Acceptor;

interface DatagridInterface
{
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
     * Setter for acceptor object
     *
     * @param Acceptor $acceptor
     *
     * @return $this
     */
    public function setAcceptor(Acceptor $acceptor);

    /**
     * Converts datasource into the result array
     * return array (
     *    'results' => converted source
     *    ....      => some additional info added by extensions
     * )
     *
     * @return ResultsIterableObject
     */
    public function getData();

    /**
     * Retrieve metadata from all extensions
     * Metadata needed to create view layer
     *
     * @return MetadataIterableObject
     */
    public function getMetadata();
}
