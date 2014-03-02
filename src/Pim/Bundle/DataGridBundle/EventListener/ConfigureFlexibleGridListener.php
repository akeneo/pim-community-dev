<?php

namespace Pim\Bundle\DataGridBundle\EventListener;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\DataGridBundle\Datasource\ProductDatasource;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ConfigurationRegistry;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ConfiguratorInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ColumnsConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Product\SortersConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Product\FiltersConfigurator;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

/**
 * Grid listener to configure column, filter and sorter based on attributes and business rules
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigureFlexibleGridListener
{
    /**
     * @var FlexibleManager
     */
    protected $flexibleManager;

    /**
     * @var ConfigurationRegistry
     */
    protected $confRegistry;

    /**
     * @var RequestParameters
     */
    protected $requestParams;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Constructor
     *
     * @param FlexibleManager          $flexibleManager flexible manager
     * @param ConfigurationRegistry    $confRegistry    attribute type configuration registry
     * @param RequestParameters        $requestParams   request parameters
     * @param SecurityContextInterface $securityContext the security context
     */
    public function __construct(
        FlexibleManager $flexibleManager,
        ConfigurationRegistry $confRegistry,
        RequestParameters $requestParams,
        SecurityContextInterface $securityContext
    ) {
        $this->flexibleManager = $flexibleManager;
        $this->confRegistry    = $confRegistry;
        $this->requestParams   = $requestParams;
        $this->securityContext = $securityContext;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * Check whenever grid is flexible and add flexible columns dynamically
     *
     * @param BuildBefore $event
     *
     * @throws \LogicException
     */
    public function buildBefore(BuildBefore $event)
    {
        $datagridConfig = $event->getConfig();

        $this->getContextConfigurator($datagridConfig)->configure();
        $this->getColumnsConfigurator($datagridConfig)->configure();
        $this->getSortersConfigurator($datagridConfig)->configure();
        $this->getFiltersConfigurator($datagridConfig)->configure();
    }

    /**
     * @param DatagridConfiguration $datagridConfig
     *
     * @return string
     */
    protected function getEntity(DatagridConfiguration $datagridConfig)
    {
        return $datagridConfig->offsetGetByPath(ProductDatasource::ENTITY_PATH);
    }

    /**
     * @param DatagridConfiguration $datagridConfig
     *
     * @return ConfiguratorInterface
     */
    protected function getContextConfigurator(DatagridConfiguration $datagridConfig)
    {
        return new ContextConfigurator(
            $datagridConfig,
            $this->flexibleManager,
            $this->requestParams,
            $this->request,
            $this->securityContext
        );
    }

    /**
     * @param DatagridConfiguration $datagridConfig
     *
     * @return ConfiguratorInterface
     */
    protected function getColumnsConfigurator(DatagridConfiguration $datagridConfig)
    {
        return new ColumnsConfigurator($datagridConfig, $this->confRegistry);
    }

    /**
     * @param DatagridConfiguration $datagridConfig
     *
     * @return ConfiguratorInterface
     */
    protected function getSortersConfigurator(DatagridConfiguration $datagridConfig)
    {
        return new SortersConfigurator($datagridConfig, $this->confRegistry, $this->flexibleManager);
    }

    /**
     * @param DatagridConfiguration $datagridConfig
     *
     * @return ConfiguratorInterface
     */
    protected function getFiltersConfigurator(DatagridConfiguration $datagridConfig)
    {
        $flexibleEntity = $this->getEntity($datagridConfig);

        return new FiltersConfigurator($datagridConfig, $this->confRegistry, $flexibleEntity);
    }
}
