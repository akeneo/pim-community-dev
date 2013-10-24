<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Extension\Acceptor;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\ExtensionVisitorInterface;

class Datagrid implements DatagridInterface
{
    /** @var ExtensionVisitorInterface[] */
    protected $extensions = [];

    /** @var DatasourceInterface */
    protected $datasource;

    /** @var string */
    protected $name;

    /** @var Acceptor */
    protected $acceptor;

    public function __construct($name, Acceptor $acceptor)
    {
        $this->name     = $name;
        $this->acceptor = $acceptor;
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
        $result = new \stdClass();

        /** @var array $rows */
        $rows = $this->getAcceptedDatasource()->getResults();

        $result->rows = $rows;
        $this->acceptor->acceptResult($this, $result);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadata()
    {
        // create \stdClass from array
        $data = (object)[self::METADATA_OPTIONS_KEY => ['gridName' => $this->getName()]];

        $this->acceptor->acceptMetadata($this, $data);

        return (array)$data;
    }

    /**
     * {@inheritDoc}
     */
    public function addExtension(ExtensionVisitorInterface $extension)
    {
        $this->extensions[] = clone $extension;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * {@inheritDoc}
     */
    public function setDatasource(DatasourceInterface $source)
    {
        $this->datasource = clone $source;

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
        $this->acceptor->acceptDatasourceVisitors($this);

        return $this->getDatasource();
    }

    /**
     * {@inheritDoc}
     */
    public function getAcceptor()
    {
        return $this->acceptor;
    }
}
