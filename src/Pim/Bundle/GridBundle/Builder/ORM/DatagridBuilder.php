<?php

namespace Pim\Bundle\GridBundle\Builder\ORM;

use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;

use Oro\Bundle\GridBundle\Route\RouteGeneratorInterface;

use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

use Oro\Bundle\GridBundle\Action\ActionFactoryInterface;

use Oro\Bundle\GridBundle\Sorter\SorterFactoryInterface;

use Oro\Bundle\GridBundle\Filter\FilterFactoryInterface;

use Oro\Bundle\UserBundle\Acl\ManagerInterface;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Symfony\Component\Form\FormFactoryInterface;

use Oro\Bundle\GridBundle\Builder\ORM\DatagridBuilder as OroDatagridBuilder;

class DatagridBuilder extends OroDatagridBuilder
{
    protected $serializer;

    public function __construct(
        FormFactoryInterface $formFactory,
        EventDispatcherInterface $eventDispatcher,
        ManagerInterface $aclManager,
        FilterFactoryInterface $filterFactory,
        SorterFactoryInterface $sorterFactory,
        ActionFactoryInterface $actionFactory,
        $className,
        $serializer = null
    ) {
        parent::__construct($formFactory, $eventDispatcher, $aclManager, $filterFactory, $sorterFactory, $actionFactory, $className);

        $this->serializer = $serializer;
    }

    public function getBaseDatagrid(
        ProxyQueryInterface $proxyQuery,
        FieldDescriptionCollection $fieldCollection,
        RouteGeneratorInterface $routeGenerator,
        ParametersInterface $parameters,
        $name
    ) {
        $datagrid = parent::getBaseDatagrid($proxyQuery, $fieldCollection, $routeGenerator, $parameters, $name);

        return $datagrid->setSerializer($this->serializer);
    }
}
