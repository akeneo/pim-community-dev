<?php

namespace Pim\Bundle\DataGridBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Pim\Bundle\DataGridBundle\Datagrid\Flexible\ConfigurationRegistry;
use Pim\Bundle\DataGridBundle\Datagrid\Flexible\ConfiguratorInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Flexible\ColumnsConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Flexible\SortersConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Flexible\FiltersConfigurator;
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
     * @var string
     */
    const IS_FLEXIBLE_ENTITY_PATH = '[source][is_flexible]';

    /**
     * @var string
     */
    const ENTITY_PATH = '[source][entity]';

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
     * @var Request
     */
    protected $request;

    /**
     * Constructor
     *
     * @param FlexibleManager       $flexibleManager flexible manager
     * @param ConfigurationRegistry $confRegistry    attribute type configuration registry
     * @param RequestParameters     $requestParams   request parameters
     */
    public function __construct(
        FlexibleManager $flexibleManager,
        ConfigurationRegistry $confRegistry,
        RequestParameters $requestParams
    ) {
        $this->flexibleManager = $flexibleManager;
        $this->confRegistry    = $confRegistry;
        $this->requestParams   = $requestParams;
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
        $isFlexibleGrid = $datagridConfig->offsetGetByPath(self::IS_FLEXIBLE_ENTITY_PATH);

        if ($isFlexibleGrid) {
            $this->prepareFlexibleManager();
            $attributes = $this->getAttributesConfig();
            $this->getColumnsConfigurator($datagridConfig, $attributes)->configure();
            $this->getSortersConfigurator($datagridConfig, $attributes)->configure();
            $this->getFiltersConfigurator($datagridConfig, $attributes)->configure();
        }
    }

    /**
     * @param DatagridConfiguration $datagridConfig
     *
     * @return string
     */
    protected function getEntity(DatagridConfiguration $datagridConfig)
    {
        return $datagridConfig->offsetGetByPath(self::ENTITY_PATH);
    }

    /**
     * @param DatagridConfiguration $datagridConfig
     * @param array()               $attributes
     *
     * @return ConfiguratorInterface
     */
    protected function getColumnsConfigurator(DatagridConfiguration $datagridConfig, $attributes)
    {
        return new ColumnsConfigurator($datagridConfig, $this->confRegistry, $attributes);
    }

    /**
     * @param DatagridConfiguration $datagridConfig
     * @param array                 $attributes
     *
     * @return ConfiguratorInterface
     */
    protected function getSortersConfigurator(DatagridConfiguration $datagridConfig, $attributes)
    {
        $sorterCallback = $this->getFlexibleSorterApplyCallback();

        return new SortersConfigurator($datagridConfig, $this->confRegistry, $attributes, $sorterCallback);
    }

    /**
     * @param DatagridConfiguration $datagridConfig
     * @param array                 $attributes
     *
     * @return ConfiguratorInterface
     */
    protected function getFiltersConfigurator(DatagridConfiguration $datagridConfig, $attributes)
    {
        $flexibleEntity = $this->getEntity($datagridConfig);

        return new FiltersConfigurator($datagridConfig, $this->confRegistry, $attributes, $flexibleEntity);
    }

    /**
     * Get attributes configuration
     *
     * @return array
     */
    protected function getAttributesConfig()
    {
        $attributeRepository = $this->flexibleManager->getAttributeRepository();
        $attributes = $attributeRepository->getAttributesGridConfig($this->flexibleManager->getFlexibleName());

        return $attributes;
    }

    /**
     * Prepare flexible manager
     */
    protected function prepareFlexibleManager()
    {
        $this->flexibleManager->setLocale($this->requestParams->getLocale());
    }

    /**
     * Creates sorter apply callback
     *
     * @return callable
     */
    protected function getFlexibleSorterApplyCallback()
    {
        $flexManager = $this->flexibleManager;

        return function (OrmDatasource $datasource, $attributeCode, $direction) use ($flexManager) {
            $qb = $datasource->getQueryBuilder();

            /** @var $entityRepository FlexibleEntityRepository */
            $entityRepository = $flexManager->getFlexibleRepository();
            $entityRepository->applySorterByAttribute($qb, $attributeCode, $direction);
        };
    }
}
