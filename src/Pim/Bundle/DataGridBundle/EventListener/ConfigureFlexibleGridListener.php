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
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;

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
     * @var FlexibleManagerRegistry
     */
    protected $flexRegistry;

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
     * @param FlexibleManagerRegistry $flexRegistry  flexible manager registry
     * @param ConfigurationRegistry   $confRegistry  attribute type configuration registry
     * @param RequestParameters       $requestParams request parameters
     */
    public function __construct(
        FlexibleManagerRegistry $flexRegistry,
        ConfigurationRegistry $confRegistry,
        RequestParameters $requestParams
    ) {
        $this->flexRegistry  = $flexRegistry;
        $this->confRegistry  = $confRegistry;
        $this->requestParams = $requestParams;
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
            $flexibleEntity = $this->getEntity($datagridConfig);
            $flexManager    = $this->getFlexibleManager($flexibleEntity);
            $attributes     = $this->getFlexibleAttributes($flexibleEntity);
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
     * @param AbstractAttribute[]   $attributes
     *
     * @return ConfiguratorInterface
     */
    protected function getColumnsConfigurator(DatagridConfiguration $datagridConfig, $attributes)
    {
        return new ColumnsConfigurator($datagridConfig, $this->confRegistry, $attributes);
    }

    /**
     * @param DatagridConfiguration $datagridConfig
     * @param AbstractAttribute[]   $attributes
     *
     * @return ConfiguratorInterface
     */
    protected function getSortersConfigurator(DatagridConfiguration $datagridConfig, $attributes)
    {
        $flexibleEntity = $this->getEntity($datagridConfig);
        $sorterCallback = $this->getFlexibleSorterApplyCallback($flexibleEntity);

        return new SortersConfigurator($datagridConfig, $this->confRegistry, $attributes, $sorterCallback);
    }

    /**
     * @param DatagridConfiguration $datagridConfig
     * @param AbstractAttribute[]   $attributes
     *
     * @return ConfiguratorInterface
     */
    protected function getFiltersConfigurator(DatagridConfiguration $datagridConfig, $attributes)
    {
        $flexibleEntity = $this->getEntity($datagridConfig);

        return new FiltersConfigurator($datagridConfig, $this->confRegistry, $attributes, $flexibleEntity);
    }

    /**
     * Prepares attributes array for given entity
     *
     * @param string $entityFQCN
     *
     * @return AbstractAttribute[]
     */
    protected function getFlexibleAttributes($entityFQCN)
    {
        $flexManager = $this->getFlexibleManager($entityFQCN);
        $attributeRepository = $flexManager->getAttributeRepository();
        $attributes = $attributeRepository->getAttributesGridConfig($entityFQCN);

        return $attributes;
    }

    /**
     * @param string $entityFQCN
     *
     * @return FlexibleManager
     */
    protected function getFlexibleManager($entityFQCN)
    {
        $flexManager = $this->flexRegistry->getManager($entityFQCN);

        $flexManager->setLocale($this->requestParams->getLocale());

        return $flexManager;
    }

    /**
     * Creates sorter apply callback
     *
     * @param string $entityFQCN
     *
     * @return callable
     */
    protected function getFlexibleSorterApplyCallback($entityFQCN)
    {
        $flexManager = $this->getFlexibleManager($entityFQCN);

        return function (OrmDatasource $datasource, $attributeCode, $direction) use ($flexManager) {
            $qb = $datasource->getQueryBuilder();

            /** @var $entityRepository FlexibleEntityRepository */
            $entityRepository = $flexManager->getFlexibleRepository();
            $entityRepository->applySorterByAttribute($qb, $attributeCode, $direction);
        };
    }
}
