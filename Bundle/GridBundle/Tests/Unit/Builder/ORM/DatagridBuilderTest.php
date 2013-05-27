<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Builder\ORM;

use Oro\Bundle\GridBundle\Builder\ORM\DatagridBuilder;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;

class DatagridBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * Test parameters
     */
    const TEST_ENTITY_NAME   = 'test_entity_name';
    const TEST_ENTITY_TYPE   = 'test_entity_type';
    const TEST_ACL_RESOURCE  = 'test_acl_resource';
    const TEST_HINT          = 'test_hint';
    /**#@-*/

    /**
     * Datagrid class name
     */
    const DATAGRID_CLASS = 'Oro\Bundle\GridBundle\Datagrid\Datagrid';

    /**
     * @var DatagridBuilder
     */
    protected $model;

    /**
     * @var array
     */
    protected $testFilterOptions = array(
        'option1'     => 'value1',
        'option2'     => 'value2',
        'filter_type' => self::TEST_ENTITY_TYPE
    );

    /**
     * @var array
     */
    protected $testActionOptions = array('key' => 'value');

    protected function tearDown()
    {
        unset($this->model);
    }

    /**
     * @param array $arguments
     */
    protected function initializeDatagridBuilder($arguments = array())
    {
        $defaultArguments = array(
            'formFactory'     => $this->getMockForAbstractClass('Symfony\Component\Form\FormFactoryInterface'),
            'eventDispatcher' => $this->getMockForAbstractClass(
                'Symfony\Component\EventDispatcher\EventDispatcherInterface'
            ),
            'filterFactory'   => $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Filter\FilterFactoryInterface'),
            'sorterFactory'   => $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Sorter\SorterFactoryInterface'),
            'actionFactory'   => $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Action\ActionFactoryInterface'),
            'className'       => null,
        );

        $arguments = array_merge($defaultArguments, $arguments);

        $this->model = new DatagridBuilder(
            $arguments['formFactory'],
            $arguments['eventDispatcher'],
            $arguments['filterFactory'],
            $arguments['sorterFactory'],
            $arguments['actionFactory'],
            $arguments['className']
        );
    }

    public function testAddFilter()
    {
        // test filter
        $testFilter = $this->getMockForAbstractClass('Sonata\AdminBundle\Filter\FilterInterface');

        // field description
        $fieldDescription = new FieldDescription();
        $fieldDescription->setName(self::TEST_ENTITY_NAME);
        $fieldDescription->setOptions($this->testFilterOptions);

        // filter factory
        $filterFactoryMock = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Filter\FilterFactoryInterface',
            array(),
            '',
            false,
            true,
            true,
            array('create')
        );
        $filterFactoryMock->expects($this->once())
            ->method('create')
            ->with(self::TEST_ENTITY_NAME, self::TEST_ENTITY_TYPE, $fieldDescription->getOptions())
            ->will($this->returnValue($testFilter));

        // datagrid
        $datagridMock = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Datagrid\DatagridInterface',
            array(),
            '',
            false,
            true,
            true,
            array('addFilter')
        );
        $datagridMock->expects($this->once())
            ->method('addFilter')
            ->with($testFilter);

        // test
        $this->initializeDatagridBuilder(array('filterFactory' => $filterFactoryMock));
        $this->model->addFilter($datagridMock, $fieldDescription);
    }

    public function testAddSorter()
    {
        // test sorter
        $testSorter = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Sorter\SorterInterface');

        // field description
        $fieldDescription = new FieldDescription();

        // sorter factory
        $sorterFactoryMock = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Sorter\SorterFactoryInterface',
            array(),
            '',
            false,
            true,
            true,
            array('create')
        );
        $sorterFactoryMock->expects($this->once())
            ->method('create')
            ->with($fieldDescription)
            ->will($this->returnValue($testSorter));

        // datagrid
        $datagridMock = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Datagrid\DatagridInterface',
            array(),
            '',
            false,
            true,
            true,
            array('addSorter')
        );
        $datagridMock->expects($this->once())
            ->method('addSorter')
            ->with($testSorter);

        // test
        $this->initializeDatagridBuilder(array('sorterFactory' => $sorterFactoryMock));
        $this->model->addSorter($datagridMock, $fieldDescription);
    }

    /**
     * Data provider for testAddRowAction
     *
     * @return array
     */
    public function addRowActionDataProvider()
    {
        return array(
            'granted_with_minimum_data' => array(
                '$isGranted' => true,
                '$actualParameters' => array(
                    'name' => self::TEST_ENTITY_NAME,
                    'type' => self::TEST_ENTITY_TYPE
                ),
                '$expectedParameters' => array(
                    'name'         => self::TEST_ENTITY_NAME,
                    'type'         => self::TEST_ENTITY_TYPE,
                    'acl_resource' => null,
                    'options'      => array()
                )
            ),
            'not_granted_with_full_data' => array(
                '$isGranted' => false,
                '$actualParameters' => array(
                    'name'         => self::TEST_ENTITY_NAME,
                    'type'         => self::TEST_ENTITY_TYPE,
                    'acl_resource' => self::TEST_ACL_RESOURCE,
                    'options'      => $this->testActionOptions
                ),
                '$expectedParameters' => array(
                    'name'         => self::TEST_ENTITY_NAME,
                    'type'         => self::TEST_ENTITY_TYPE,
                    'acl_resource' => self::TEST_ACL_RESOURCE,
                    'options'      => $this->testActionOptions
                )
            ),
        );
    }

    /**
     * @param boolean $isGranted
     * @param array $actualParameters
     * @param array $expectedParameters
     *
     * @dataProvider addRowActionDataProvider
     */
    public function testAddRowAction($isGranted, array $actualParameters, array $expectedParameters)
    {
        // action mock
        $actionMock = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Action\AbstractAction',
            array(),
            '',
            false,
            true,
            true,
            array('isGranted')
        );
        $actionMock->expects($this->once())
            ->method('isGranted')
            ->will($this->returnValue($isGranted));

        // action factory
        $actionFactoryMock = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Action\ActionFactoryInterface',
            array(),
            '',
            false,
            true,
            true,
            array('create')
        );
        $actionFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                $expectedParameters['name'],
                $expectedParameters['type'],
                $expectedParameters['acl_resource'],
                $expectedParameters['options']
            )
            ->will($this->returnValue($actionMock));

        // datagrid
        $datagridMock = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Datagrid\DatagridInterface',
            array(),
            '',
            false,
            true,
            true,
            array('addSorter')
        );
        if ($isGranted) {
            $datagridMock->expects($this->once())
                ->method('addRowAction')
                ->with($actionMock);
        }

        // test
        $this->initializeDatagridBuilder(array('actionFactory' => $actionFactoryMock));
        $this->model->addRowAction($datagridMock, $actualParameters);
    }

    public function testAddProperty()
    {
        $this->initializeDatagridBuilder();

        // property
        $propertyMock = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Property\PropertyInterface');

        // datagrid
        $datagridMock = $this->getMockBuilder('Oro\Bundle\GridBundle\Datagrid\DatagridInterface')
            ->setMethods(array('addProperty'))
            ->getMockForAbstractClass();

        $datagridMock->expects($this->once())
            ->method('addProperty')
            ->with($propertyMock);

        $this->model->addProperty($datagridMock, $propertyMock);
    }

    public function testGetBaseDatagrid()
    {
        // form builder
        $formBuilderMock = $this->getMock('Symfony\Component\Form\FormBuilder', array(), array(), '', false);

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
        $formFactoryMock->expects($this->once())
            ->method('createNamedBuilder')
            ->with(self::TEST_ENTITY_NAME, 'form', array(), array('csrf_protection' => false))
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

        // test datagrid
        $this->initializeDatagridBuilder(
            array(
                'formFactory' => $formFactoryMock,
                'eventDispatcher' => $eventDispatcherMock,
                'className' => self::DATAGRID_CLASS
            )
        );

        $datagrid = $this->model->getBaseDatagrid(
            $proxyQueryMock,
            $fieldDescriptionCollection,
            $routeGeneratorMock,
            $parametersMock,
            self::TEST_ENTITY_NAME,
            self::TEST_HINT
        );

        $this->assertInstanceOf(self::DATAGRID_CLASS, $datagrid);
        $this->assertAttributeEquals($proxyQueryMock, 'query', $datagrid);
        $this->assertAttributeEquals($fieldDescriptionCollection, 'columns', $datagrid);
        $this->assertAttributeEquals($formBuilderMock, 'formBuilder', $datagrid);
        $this->assertAttributeEquals($eventDispatcherMock, 'eventDispatcher', $datagrid);
        $this->assertAttributeEquals($routeGeneratorMock, 'routeGenerator', $datagrid);
        $this->assertAttributeEquals($parametersMock, 'parameters', $datagrid);
        $this->assertAttributeEquals(self::TEST_ENTITY_NAME, 'name', $datagrid);
        $this->assertAttributeEquals(self::TEST_HINT, 'entityHint', $datagrid);

        // test pager
        $pager = $datagrid->getPager();

        $this->assertInstanceOf('Oro\Bundle\GridBundle\Datagrid\ORM\Pager', $pager);
        $this->assertAttributeEquals($proxyQueryMock, 'query', $pager);
    }
}
