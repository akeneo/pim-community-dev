<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataIterableObject;
use Oro\Bundle\DataGridBundle\Datagrid\Common\ResultsIterableObject;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\Acceptor;

class Datagrid implements DatagridInterface
{
    /** @var DatasourceInterface */
    protected $datasource;

    /** @var string */
    protected $name;

    /** @var Acceptor */
    protected $acceptor;

    public function __construct(string $name, Acceptor $acceptor)
    {
        $this->name = $name;
        $this->setAcceptor($acceptor);
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getData(): ResultsIterableObject
    {
        /** @var array $rows */
        $rows = $this->getAcceptedDatasource()->getResults();

        $result = ResultsIterableObject::create(['data' => $rows]);
        $this->acceptor->acceptResult($result);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadata(): MetadataIterableObject
    {
        $data = MetadataIterableObject::createNamed($this->getName(), []);
        $this->acceptor->acceptMetadata($data);

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function setDatasource(DatasourceInterface $source): DatagridInterface
    {
        $this->datasource = $source;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDatasource(): \Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface
    {
        return $this->datasource;
    }

    /**
     * {@inheritDoc}
     */
    public function getAcceptedDatasource(): \Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface
    {
        $this->acceptor->acceptDatasource($this->getDatasource());

        return $this->getDatasource();
    }

    /**
     * {@inheritDoc}
     */
    public function getAcceptor(): \Oro\Bundle\DataGridBundle\Extension\Acceptor
    {
        return $this->acceptor;
    }

    /**
     * {@inheritDoc}
     */
    public function setAcceptor(Acceptor $acceptor): DatagridInterface
    {
        $this->acceptor = $acceptor;

        return $this;
    }
}
