<?php
namespace Oro\Bundle\FlexibleEntityBundle\Grid;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Datasource\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Extension\Formatter\FormatterExtension;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Oro\Bundle\FilterBundle\Extension\OrmFilterExtension;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Grid\Extension\Formatter\Property\FlexibleFieldProperty;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class EventListener
{
    const FLEXIBLE_COLUMNS_PATH = '[flexible_attributes]';
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
        $flexible = $this->accessor->getValue($config, self::FLEXIBLE_COLUMNS_PATH);

        if ($flexible) {
            $this->validateConfiguration(new Configuration(), array('flexible_attributes' => $flexible));
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
                $filterable = $this->accessor->getValue($definition, '[filterable]') ? : false;
                $showFilter = $this->accessor->getValue($definition, '[show_filter]') ? : true;

                if (!isset($attributes[$attribute])) {
                    throw new \LogicException(sprintf('Flexible attribute "%s" does not exist', $attribute));
                }
                $this->accessor->setValue(
                    $config,
                    sprintf('%s[%s]', FormatterExtension::COLUMNS_PATH, $attribute),
                    [
                        'type'                                  => 'flexible_field',
                        'backend_type'                          => $attributes[$attribute]->getBackendType(),
                        PropertyInterface::FRONTEND_OPTIONS_KEY => [
                            'label' => $attributes[$attribute]->getLabel()
                        ]
                    ]
                );

                if ($filterable) {
                    $filterType = isset(FlexibleFieldProperty::$typeMatches[$attributes[$attribute]->getBackendType()])
                        ? FlexibleFieldProperty::$typeMatches[$attributes[$attribute]->getBackendType()]['filter']
                        : FlexibleFieldProperty::$typeMatches[AbstractAttributeType::BACKEND_TYPE_TEXT];

                    $this->accessor->setValue(
                        $config,
                        OrmFilterExtension::COLUMNS_PATH . '[' . $attribute . ']',
                        [
                            'type'                 => $filterType,
                            'flexible_entity_name' => $flexibleEntity,
                            'data_name'            => $attribute,
                            'options'              => ['show_filter' => $showFilter]
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
        $fields   = $this->accessor->getValue($config, FormatterExtension::COLUMNS_PATH) ? : [];

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
