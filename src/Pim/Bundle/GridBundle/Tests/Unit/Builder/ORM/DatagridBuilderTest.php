<?php

namespace Pim\Bundle\GridBundle\Tests\Unit\Builder\ORM;

use Pim\Bundle\GridBundle\Builder\ORM\DatagridBuilder;

use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\GridBundle\Tests\Unit\Builder\ORM\DatagridBuilderTest as OroDatagridBuilderTest;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridBuilderTest extends OroDatagridBuilderTest
{
    /**
     * @staticvar string
     */
    const DATAGRID_CLASS = 'Pim\Bundle\GridBundle\Datagrid\Datagrid';

    /**
     * {@inheritdoc}
     */
    protected function initializeDatagridBuilder($arguments = array())
    {
        $arguments = $this->getDatagridBuilderArguments($arguments);

        $this->model = new DatagridBuilder(
            $arguments['formFactory'],
            $arguments['eventDispatcher'],
            $arguments['securityFacade'],
            $arguments['filterFactory'],
            $arguments['sorterFactory'],
            $arguments['actionFactory'],
            $arguments['className'],
            $arguments['serializer']
        );
    }

    /**
     * @param array $arguments
     *
     * @return array
     */
    protected function getDatagridBuilderArguments(array $arguments = array())
    {
        $defaultArguments = array(
            'formFactory'     => $this->getMockForAbstractClass('Symfony\Component\Form\FormFactoryInterface'),
            'eventDispatcher' => $this->getMockForAbstractClass(
                'Symfony\Component\EventDispatcher\EventDispatcherInterface'
            ),
            'securityFacade'  => $this->getMockBuilder('Oro\Bundle\SecurityBundle\SecurityFacade')
                                 ->disableOriginalConstructor()->getMock(),
            'filterFactory'   => $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Filter\FilterFactoryInterface'),
            'sorterFactory'   => $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Sorter\SorterFactoryInterface'),
            'actionFactory'   => $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Action\ActionFactoryInterface'),
            'className'       => null,
            'serializer'      => $this->getMockForAbstractClass('Symfony\Component\Serializer\Serializer')
        );

        return array_merge($defaultArguments, $arguments);
    }

    /**
     * {@inheritdoc}
     *
     * Redefine method to use own datagrid class
     */
    public function testGetBaseDatagrid()
    {
        // filter form
        $filterForm = $this->getMock('Symfony\Component\Form\Form', array(), array(), '', false);

        // form builder
        $formBuilderMock = $this->getMock('Symfony\Component\Form\FormBuilder', array('getForm'), array(), '', false);
        $formBuilderMock->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($filterForm));

        // form factory
        $formFactoryMock = $this->getMockForAbstractClass(
            'Symfony\Component\Form\FormFactoryInterface',
            array(),
            '',
            false,
            true,
            true,
            array('createNamedBuilder')
        );
        $formFactoryMock
            ->expects($this->once())
            ->method('createNamedBuilder')
            ->with(static::TEST_ENTITY_NAME, 'form', array(), array('csrf_protection' => false))
            ->will($this->returnValue($formBuilderMock));
        $eventDispatcherMock = $this->getMockForAbstractClass(
            'Symfony\Component\EventDispatcher\EventDispatcherInterface',
            array(),
            '',
            false
        );

        // datagrid input parameters
        $proxyQueryMock= $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface');
        $fieldDescriptionCollection = new FieldDescriptionCollection();
        $routeGeneratorMock = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Route\RouteGeneratorInterface');
        $parametersMock = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Datagrid\ParametersInterface');
        $parametersMock->expects($this->at(0))
            ->method('get')
            ->with(ParametersInterface::FILTER_PARAMETERS)
            ->will($this->returnValue(array()));
        $parametersMock->expects($this->at(1))
            ->method('get')
            ->with(ParametersInterface::PAGER_PARAMETERS)
            ->will($this->returnValue(array()));
        $parametersMock->expects($this->at(2))
            ->method('get')
            ->with(ParametersInterface::SORT_PARAMETERS)
            ->will($this->returnValue(array()));

        // test datagrid
        $this->initializeDatagridBuilder(
            array(
                'formFactory' => $formFactoryMock,
                'eventDispatcher' => $eventDispatcherMock,
                'className' => static::DATAGRID_CLASS
            )
        );

        $datagrid = $this->model->getBaseDatagrid(
            $proxyQueryMock,
            $fieldDescriptionCollection,
            $routeGeneratorMock,
            $parametersMock,
            static::TEST_ENTITY_NAME
        );

        $this->assertInstanceOf(static::DATAGRID_CLASS, $datagrid);
        $this->assertAttributeEquals($proxyQueryMock, 'query', $datagrid);
        $this->assertAttributeEquals($fieldDescriptionCollection, 'columns', $datagrid);
        $this->assertAttributeEquals($formBuilderMock, 'formBuilder', $datagrid);
        $this->assertAttributeEquals($eventDispatcherMock, 'eventDispatcher', $datagrid);
        $this->assertAttributeEquals($routeGeneratorMock, 'routeGenerator', $datagrid);
        $this->assertAttributeEquals($parametersMock, 'parameters', $datagrid);

        // test pager
        $pager = $datagrid->getPager();

        $this->assertInstanceOf('Oro\Bundle\GridBundle\Datagrid\ORM\Pager', $pager);
        $this->assertAttributeEquals($proxyQueryMock, 'query', $pager);
    }
}
