<?php
namespace Pim\Bundle\FlexibleEntityBundle\Grid;

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
use Pim\Bundle\FlexibleEntityBundle\Grid\Extension\Filter\FilterUtility;
use Pim\Bundle\FlexibleEntityBundle\Grid\Extension\Formatter\Property\FlexibleFieldProperty;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class EventListener
{
    const SCOPE_PARAMETER      = '_scope';
    const FLEXIBLE_ENTITY_PATH = '[flexible_entity]';

    /** @var  PropertyAccessor */
    protected $accessor;

    /** @var FlexibleManagerRegistry */
    protected $registry;

    /** @var RequestParameters */
    protected $requestParams;

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
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function buildBefore(BuildBefore $event)
    {
        $config   = $event->getConfig();
        $flexible = $config->offsetGetOr(Configuration::FLEXIBLE_ATTRIBUTES_KEY);

        if ($flexible) {
            Object::create([Configuration::FLEXIBLE_ATTRIBUTES_KEY => $flexible])
                ->validateConfiguration(new Configuration());

            $flexibleEntity = $config->offsetGetByPath(self::FLEXIBLE_ENTITY_PATH);
            if (!$flexibleEntity) {
                throw new \LogicException(
                    'Could not retrieve "flexible_entity" attribute for datagrid: ' . $event->getDatagrid()->getName()
                );
            }

            $attributes = $this->getFlexibleAttributes($flexibleEntity);
            foreach ($flexible as $attribute => $definition) {
                $definition    = $definition ? : [];
                $sortable      = $this->accessor->getValue($definition, '[sortable]') ? : false;
                $filterable    = $this->accessor->getValue($definition, '[filterable]') ? : false;
                $enabledFilter = $this->accessor->getValue($definition, '[filter_enabled]') ? : true;

                if (!isset($attributes[$attribute])) {
                    throw new \LogicException(sprintf('Flexible attribute "%s" does not exist', $attribute));
                }
                $config->offsetSetByPath(
                    sprintf('[%s][%s]', FormatterConfiguration::COLUMNS_KEY, $attribute),
                    [
                        FlexibleFieldProperty::TYPE_KEY         => 'flexible_field',
                        FlexibleFieldProperty::BACKEND_TYPE_KEY => $attributes[$attribute]->getBackendType(),
                        'label'                                 => $attributes[$attribute]->getLabel()
                    ]
                );

                if ($filterable) {
                    $map         = FlexibleFieldProperty::$typeMatches;
                    $backendType = $attributes[$attribute]->getBackendType();

                    $filterType = isset(FlexibleFieldProperty::$typeMatches[$backendType])
                        ? $map[$backendType]['filter']
                        : $map[AbstractAttributeType::BACKEND_TYPE_TEXT]['filter'];

                    $parentType = isset(FlexibleFieldProperty::$typeMatches[$backendType])
                        ? $map[$backendType]['parent_filter']
                        : $map[AbstractAttributeType::BACKEND_TYPE_TEXT]['parent_filter'];

                    $config->offsetSetByPath(
                        sprintf('%s[%s]', FilterConfiguration::COLUMNS_PATH, $attribute),
                        [
                            FilterUtility::TYPE_KEY        => $filterType,
                            FilterUtility::FEN_KEY         => $flexibleEntity,
                            FilterUtility::DATA_NAME_KEY   => $attribute,
                            FilterUtility::PARENT_TYPE_KEY => $parentType,
                            FilterUtility::ENABLED_KEY     => $enabledFilter
                        ]
                    );
                }

                if ($sortable) {
                    $config->offsetSetByPath(
                        sprintf('%s[%s]', OrmSorterConfiguration::COLUMNS_PATH, $attribute),
                        [
                            PropertyInterface::DATA_NAME_KEY => $attribute,
                            'apply_callback'                 => $this->getFlexibleSorterApplyCallback($flexibleEntity)
                        ]
                    );
                }
            }
        }
    }

    /**
     * Adds entity object to datasource query builder
     *
     * @param BuildAfter $event
     */
    public function buildAfter(BuildAfter $event)
    {
        $datagrid = $event->getDatagrid();
        $config   = $datagrid->getAcceptor()->getConfig();
        $fields   = $config->offsetGetOr(FormatterConfiguration::COLUMNS_KEY, []);

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
     * @param $entityFQCN string
     *
     * @return AbstractAttribute[]
     */
    protected function getFlexibleAttributes($entityFQCN)
    {
        $fm = $this->getFlexibleManager($entityFQCN);

        $attributeRepository = $fm->getAttributeRepository();
        $attributesEntities  = $attributeRepository->findBy(['entityType' => $fm->getFlexibleName()]);
        $attributes          = [];
        /** @var $attribute AbstractAttribute */
        foreach ($attributesEntities as $attribute) {
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
        $fm = $this->registry->getManager($entityFQCN);

        $rootValue = $this->requestParams->getRootParameterValue();
        $scope     = isset($rootValue[self::SCOPE_PARAMETER]) ? $rootValue[self::SCOPE_PARAMETER] : null;

        $fm->setLocale($this->requestParams->getLocale())->setScope($scope);

        return $fm;
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
        $fm = $this->getFlexibleManager($entityFQCN);

        return function (OrmDatasource $datasource, $attribute, $direction) use ($fm) {
            $qb = $datasource->getQueryBuilder();

            /** @var $entityRepository FlexibleEntityRepository */
            $entityRepository = $fm->getFlexibleRepository();
            $entityRepository->applySorterByAttribute($qb, $attribute, $direction);
        };
    }
}
