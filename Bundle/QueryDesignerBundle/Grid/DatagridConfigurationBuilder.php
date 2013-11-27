<?php

namespace Oro\Bundle\QueryDesignerBundle\Grid;

use Symfony\Bridge\Doctrine\ManagerRegistry;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner;
use Oro\Bundle\QueryDesignerBundle\Exception\InvalidConfigurationException;

class DatagridConfigurationBuilder
{
    /**
     * @var DatagridConfigurationQueryConverter
     */
    protected $converter;

    /**
     * @var DatagridConfiguration
     */
    protected $config;

    /**
     * Constructor
     *
     * @param string                $gridName
     * @param AbstractQueryDesigner $source
     * @param ManagerRegistry       $doctrine
     * @throws InvalidConfigurationException
     */
    public function __construct($gridName, AbstractQueryDesigner $source, ManagerRegistry $doctrine)
    {
        $this->converter = new DatagridConfigurationQueryConverter($doctrine);
        $this->config    = $this->converter->convert($gridName, $source);
    }

    /**
     * Return a datagrid configuration
     *
     * @return DatagridConfiguration
     */
    public function getConfiguration()
    {
        return $this->config;
    }
}
