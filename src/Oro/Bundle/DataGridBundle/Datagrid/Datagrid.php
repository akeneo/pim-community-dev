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

    public function __construct($name, Acceptor $acceptor)
    {
        $this->name = $name;
        $this->setAcceptor($acceptor);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
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
    public function getMetadata()
    {
        $data = MetadataIterableObject::createNamed($this->getName(), []);
        $this->acceptor->acceptMetadata($data);

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function setDatasource(DatasourceInterface $source)
    {
        $this->datasource = $source;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDatasource()
    {
        return $this->datasource;
    }

    /**
     * {@inheritDoc}
     */
    public function getAcceptedDatasource()
    {
        $this->acceptor->acceptDatasource($this->getDatasource());

        return $this->getDatasource();
    }

    /**
     * {@inheritDoc}
     */
    public function getAcceptor()
    {
        return $this->acceptor;
    }

    /**
     * {@inheritDoc}
     */
    public function setAcceptor(Acceptor $acceptor)
    {
        $this->acceptor = $acceptor;

        return $this;
    }
}
