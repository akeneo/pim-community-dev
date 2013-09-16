<?php

namespace Oro\Bundle\GridBundle\Builder;

use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Property\PropertyInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\GridBundle\Route\RouteGeneratorInterface;
use Oro\Bundle\GridBundle\Filter\FilterFactoryInterface;
use Oro\Bundle\GridBundle\Sorter\SorterFactoryInterface;
use Oro\Bundle\GridBundle\Action\ActionFactoryInterface;
use Oro\Bundle\GridBundle\Datagrid\PagerInterface;
use Oro\Bundle\GridBundle\Action\MassAction\MassActionInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;

abstract class AbstractDatagridBuilder implements DatagridBuilderInterface
{
    /**
     * @var FilterFactoryInterface
     */
    protected $filterFactory;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var SorterFactoryInterface
     */
    protected $sorterFactory;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var ActionFactoryInterface
     */
    protected $actionFactory;

    /**
     * @var SecurityFacade
     */
    protected $securityFacade;

    /**
     * @var string
     */
    protected $className;

    /**
     * @param FormFactoryInterface $formFactory
     * @param EventDispatcherInterface $eventDispatcher
     * @param SecurityFacade $securityFacade
     * @param FilterFactoryInterface $filterFactory
     * @param SorterFactoryInterface $sorterFactory
     * @param ActionFactoryInterface $actionFactory
     * @param $className
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        EventDispatcherInterface $eventDispatcher,
        SecurityFacade         $securityFacade,
        FilterFactoryInterface $filterFactory,
        SorterFactoryInterface $sorterFactory,
        ActionFactoryInterface $actionFactory,
        $className
    ) {
        //ManagerInterface $aclManager,
        $this->formFactory     = $formFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->securityFacade  = $securityFacade;
        $this->filterFactory   = $filterFactory;
        $this->sorterFactory   = $sorterFactory;
        $this->actionFactory   = $actionFactory;
        $this->className       = $className;
    }

    /**
     * @param DatagridInterface $datagrid
     * @param FieldDescriptionInterface $fieldDescription
     * @return void
     */
    public function addFilter(
        DatagridInterface $datagrid,
        FieldDescriptionInterface $fieldDescription = null
    ) {
        $filter = $this->filterFactory->create(
            $fieldDescription->getName(),
            $fieldDescription->getOption('filter_type'),
            $fieldDescription->getOptions()
        );
        $filter->setOption('data_type', $fieldDescription->getType());
        $datagrid->addFilter($filter);
    }

    /**
     * {@inheritdoc}
     */
    public function addSelectedRowFilter(DatagridInterface $datagrid, array $options)
    {
        $filter = $this->filterFactory->create(
            self::SELECTED_ROW_FILTER_NAME,
            FilterInterface::TYPE_SELECT_ROW,
            $options
        );
        $filter->setOption('data_type', FieldDescriptionInterface::TYPE_INTEGER);
        // filter must be first in list
        $datagrid->addFilter($filter, true);
    }

    /**
     * @param DatagridInterface $datagrid
     * @param FieldDescriptionInterface $field
     */
    public function addSorter(DatagridInterface $datagrid, FieldDescriptionInterface $field)
    {
        $sorter = $this->sorterFactory->create($field);

        $datagrid->addSorter($sorter);
    }

    /**
     * @param DatagridInterface $datagrid
     * @param array $parameters
     */
    public function addRowAction(DatagridInterface $datagrid, array $parameters)
    {
        $action = $this->actionFactory->create(
            $parameters['name'],
            $parameters['type'],
            isset($parameters['acl_resource']) ? $parameters['acl_resource'] : null,
            isset($parameters['options']) ? $parameters['options'] : array()
        );

        $aclResource = $action->getAclResource();

        if (!$aclResource || $this->securityFacade->isGranted($aclResource)) {
            $datagrid->addRowAction($action);
        }
    }

    /**
     * @param DatagridInterface $datagrid
     * @param MassActionInterface $massAction
     */
    public function addMassAction(DatagridInterface $datagrid, MassActionInterface $massAction)
    {
        $aclResource = $massAction->getAclResource();
        if (!$aclResource || $this->securityFacade->isGranted($aclResource)) {
            $datagrid->addMassAction($massAction);
        }
    }

    /**
     * Add property to datagrid
     *
     * @param DatagridInterface $datagrid
     * @param PropertyInterface $property
     * @return void
     */
    public function addProperty(DatagridInterface $datagrid, PropertyInterface $property)
    {
        $datagrid->addProperty($property);
    }

    /**
     * @param ProxyQueryInterface $query
     * @param FieldDescriptionCollection $fieldCollection
     * @param RouteGeneratorInterface $routeGenerator
     * @param ParametersInterface $parameters
     * @param string $name
     *
     * @return DatagridInterface
     */
    public function getBaseDatagrid(
        ProxyQueryInterface $query,
        FieldDescriptionCollection $fieldCollection,
        RouteGeneratorInterface $routeGenerator,
        ParametersInterface $parameters,
        $name
    ) {
        $formBuilder = $this->formFactory->createNamedBuilder(
            $this->getFormName($name),
            'form',
            array(),
            array('csrf_protection' => false)
        );

        $datagridClassName = $this->className;
        return new $datagridClassName(
            $query,
            $fieldCollection,
            $this->createPager($query),
            $formBuilder,
            $routeGenerator,
            $parameters,
            $this->eventDispatcher
        );
    }

    /**
     * @param string $datagridName
     * @return string
     */
    protected function getFormName($datagridName)
    {
        return $datagridName;
    }

    /**
     * @param ProxyQueryInterface $query
     * @return PagerInterface
     */
    abstract protected function createPager(ProxyQueryInterface $query);
}
