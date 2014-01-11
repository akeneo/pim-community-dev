<?php

namespace Pim\Bundle\DataGridBundle\EventListener;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Common\Object;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Oro\Bundle\DataGridBundle\Extension\Sorter\Configuration as OrmSorterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Pim\Bundle\FilterBundle\Filter\Flexible\FilterUtility;
use Pim\Bundle\DataGridBundle\Extension\Formatter\Property\FlexibleFieldProperty;
use Pim\Bundle\DataGridBundle\Datagrid\Flexible\ColumnsConfigurator;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

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

    /** @var  PropertyAccessor */
    protected $accessor;

    /** @var FlexibleManagerRegistry */
    protected $registry;

    /** @var RequestParameters */
    protected $requestParams;

    /**
     * Constructor
     *
     * @param FlexibleManagerRegistry $registry
     * @param RequestParameters       $requestParams
     */
    public function __construct(FlexibleManagerRegistry $registry, RequestParameters $requestParams)
    {
        $this->registry      = $registry;
        $this->requestParams = $requestParams;
        $this->accessor      = PropertyAccess::createPropertyAccessor();
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
        $config = $event->getConfig();
        $flexibleEntity = $config->offsetGetByPath(self::FLEXIBLE_ENTITY_PATH);

        if ($flexibleEntity) {
            $attributes = $this->getFlexibleAttributes($flexibleEntity);

            $configurator = new ColumnsConfigurator($config, $attributes);
            $configurator->configure();

            // TODO nidup refactoring of following in progress ...
            foreach ($attributes as $attributeCode => $attribute) {
                $showFilter = $attribute->isUseableAsGridFilter();
                $showColumn = $attribute->isUseableAsGridColumn();
                if (!$showFilter && !$showColumn) {
                    continue;
                }
                $sortable = $showColumn;

                $attributeType = $attribute->getAttributeType();

                if (in_array($attributeType, array('pim_catalog_file', 'pim_catalog_image'))) {
                    continue;
                }

                if ($showColumn) {
                    if ($sortable) {
                        $config->offsetSetByPath(
                            sprintf('%s[%s]', OrmSorterConfiguration::COLUMNS_PATH, $attributeCode),
                            array(
                                PropertyInterface::DATA_NAME_KEY => $attributeCode,
                                'apply_callback'                 => $this->getFlexibleSorterApplyCallback($flexibleEntity)
                            )
                        );
                    }
                }

                if ($showFilter) {
                    $map         = FlexibleFieldProperty::$typeMatches;
                    $backendType = $attribute->getBackendType();

                    $filterType = isset(FlexibleFieldProperty::$typeMatches[$backendType])
                        ? $map[$backendType]['filter']
                        : $map[AbstractAttributeType::BACKEND_TYPE_TEXT]['filter'];

                    $parentType = isset(FlexibleFieldProperty::$typeMatches[$backendType])
                        ? $map[$backendType]['parent_filter']
                        : $map[AbstractAttributeType::BACKEND_TYPE_TEXT]['parent_filter'];

                    $filterConfig = array(
                        FilterUtility::TYPE_KEY        => $filterType,
                        FilterUtility::FEN_KEY         => $flexibleEntity,
                        FilterUtility::DATA_NAME_KEY   => $attributeCode,
                        FilterUtility::PARENT_TYPE_KEY => $parentType,
                        'label'                        => $attribute->getLabel()
                    );

                    if (isset($map[$backendType]['field_options'])) {
                        $filterConfig[FilterUtility::FORM_OPTIONS_KEY] = array(
                            'field_options' => $map[$backendType]['field_options']
                        );
                    }

                    if ($backendType === 'metric') {
                        $filterConfig['family'] = $attribute->getMetricFamily();
                    }

                    $config->offsetSetByPath(
                        sprintf('%s[%s]', FilterConfiguration::COLUMNS_PATH, $attributeCode),
                        $filterConfig
                    );
                }
            }
        }
    }

    /**
     * Adds entity object to datasource query builder
     *
     * @param BuildAfter $event
     *
     * @return null
     */
    public function buildAfter(BuildAfter $event)
    {
        $datagrid = $event->getDatagrid();
        $config   = $datagrid->getAcceptor()->getConfig();
        $fields   = $config->offsetGetOr(FormatterConfiguration::COLUMNS_KEY, array());

        $flexibleCount = count(
            array_filter(
                $fields,
                function ($value) {
                    return $value['type'] === 'flexible_field';
                }
            )
        );

        if ($flexibleCount && $datagrid->getDatasource() instanceof OrmDatasource) {
            /** @var QueryBUilder $qb */
            $qb = $datagrid->getDatasource()->getQueryBuilder();

            $aliases = $qb->getRootAliases();
            $alias   = reset($aliases);

            $qb->addSelect($alias);
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
        $flexManager = $this->registry->getManager($entityFQCN);

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
