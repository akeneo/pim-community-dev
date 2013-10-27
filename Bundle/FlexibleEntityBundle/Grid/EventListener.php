<?php
namespace Oro\Bundle\FlexibleEntityBundle\Grid;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\FilterBundle\Extension\Configuration as FilterConfiguration;
use Oro\Bundle\FilterBundle\Extension\Orm\FilterInterface;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Grid\Extension\Filter\FlexibleFilterUtility;
use Oro\Bundle\FlexibleEntityBundle\Grid\Extension\Formatter\Property\FlexibleFieldProperty;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class EventListener
{
    const FLEXIBLE_ENTITY_PATH  = '[flexible_entity]';

    /** @var  PropertyAccessor */
    protected $accessor;

    /** @var FlexibleManagerRegistry */
    protected $registry;

    public function __construct(FlexibleManagerRegistry $registry)
    {
        $this->registry = $registry;
        $this->accessor = PropertyAccess::createPropertyAccessor();
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
        $config   = $event->getConfig();
        $flexible = $this->accessor->getValue($config, sprintf('[%s]', Configuration::FLEXIBLE_ATTRIBUTES_KEY));

        if ($flexible) {
            $this->validateConfiguration(new Configuration(), [Configuration::FLEXIBLE_ATTRIBUTES_KEY => $flexible]);
            $flexibleEntity = $this->accessor->getValue($config, self::FLEXIBLE_ENTITY_PATH);
            if (!$flexibleEntity) {
                throw new \LogicException(
                    'Could not retrieve "flexible_entity" attribute for datagrid: ' . $event->getDatagrid()->getName()
                );
            }

            $attributes = $this->getFlexibleAttributes($flexibleEntity);
            foreach ($flexible as $attribute => $definition) {
                $definition = $definition ? : [];
                // $sortable   = $this->accessor->getValue($definition, '[sortable]') ? : false;
                $filterable    = $this->accessor->getValue($definition, '[filterable]') ? : false;
                $enabledFilter = $this->accessor->getValue($definition, '[filter_enabled]') ? : true;

                if (!isset($attributes[$attribute])) {
                    throw new \LogicException(sprintf('Flexible attribute "%s" does not exist', $attribute));
                }
                $this->accessor->setValue(
                    $config,
                    sprintf('%s[%s]', FormatterConfiguration::COLUMNS_PATH, $attribute),
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

                    $this->accessor->setValue(
                        $config,
                        sprintf('%s[%s]', FilterConfiguration::COLUMNS_PATH, $attribute),
                        [
                            FilterConfiguration::TYPE_KEY          => $filterType,
                            FlexibleFilterUtility::FEN_KEY         => $flexibleEntity,
                            FilterInterface::DATA_NAME_KEY         => $attribute,
                            FlexibleFilterUtility::PARENT_TYPE_KEY => $parentType,
                            FilterConfiguration::ENABLED_KEY       => $enabledFilter
                        ]
                    );
                }
            }
        }

        $event->setConfig($config);
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
        $fields   = $this->accessor->getValue($config, FormatterConfiguration::COLUMNS_PATH) ? : [];

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
            $qb = $datagrid->getDatasource()->getQuery();

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
        $fm = $this->registry->getManager($entityFQCN);

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
     * @param ConfigurationInterface      $configuration
     * @param                             $config
     */
    protected function validateConfiguration(ConfigurationInterface $configuration, $config)
    {
        $processor = new Processor();
        $processor->processConfiguration(
            $configuration,
            $config
        );
    }
}
