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
     */
    public function getName(): string;

    /**
     * Set grid datasource
     *
     * @param DatasourceInterface $source
     *
     * @return $this
     */
    public function setDatasource(DatasourceInterface $source): self;

    /**
     * Returns datasource object
     */
    public function getDatasource(): \Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;

    /**
     * Returns datasource object accepted by extensions
     */
    public function getAcceptedDatasource(): \Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;

    /**
     * Getter for acceptor object
     */
    public function getAcceptor(): \Oro\Bundle\DataGridBundle\Extension\Acceptor;

    /**
     * Setter for acceptor object
     *
     * @param Acceptor $acceptor
     *
     * @return $this
     */
    public function setAcceptor(Acceptor $acceptor): self;

    /**
     * Converts datasource into the result array
     * return array (
     *    'results' => converted source
     *    ....      => some additional info added by extensions
     * )
     */
    public function getData(): ResultsIterableObject;

    /**
     * Retrieve metadata from all extensions
     * Metadata needed to create view layer
     */
    public function getMetadata(): MetadataIterableObject;
}
