<?php

namespace Pim\Bundle\DataGridBundle\EventListener;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Common\Object;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Pim\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Pim\Bundle\DataGridBundle\Datagrid\Flexible\ConfigurationRegistry;
use Pim\Bundle\DataGridBundle\Datagrid\Flexible\ColumnsConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Flexible\SortersConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Flexible\FiltersConfigurator;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;

/**
 * Grid listener for flexible attributes
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigureFlexibleGridListener
{
    const FLEXIBLE_ENTITY_PATH = '[flexible_entity]';

    /** @var FlexibleManagerRegistry */
    protected $flexRegistry;

    /** @var ConfigurationRegistry */
    protected $confRegistry;

    /** @var RequestParameters */
    protected $requestParams;

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
     * Check whenever grid is flexible and add flexible columns dynamically
     *
     * @param BuildBefore $event
     *
     * @throws \LogicException
     */
    public function buildBefore(BuildBefore $event)
    {
        $datagridConfig = $event->getConfig();
        $flexibleEntity = $datagridConfig->offsetGetByPath(self::FLEXIBLE_ENTITY_PATH);

        if ($flexibleEntity) {
            $attributes = $this->getFlexibleAttributes($flexibleEntity);

            $configurator = new ColumnsConfigurator($datagridConfig, $this->confRegistry, $attributes);
            $configurator->configure();

            $sorterCallback = $this->getFlexibleSorterApplyCallback($flexibleEntity);
            $configurator = new SortersConfigurator($datagridConfig, $this->confRegistry, $attributes, $sorterCallback);
            $configurator->configure();

            $configurator = new FiltersConfigurator($datagridConfig, $this->confRegistry, $attributes, $flexibleEntity);
            $configurator->configure();
        }
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
        $attributeEntities   = $attributeRepository->findBy(['entityType' => $flexManager->getFlexibleName()]);
        $attributes          = array();

        foreach ($attributeEntities as $attribute) {
            $attributes[$attribute->getCode()] = $attribute;
        }

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
