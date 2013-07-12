<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Datagrid;

use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\GridBundle\Datagrid\ResultRecord;
use Oro\Bundle\GridBundle\Datagrid\Datagrid;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Property\PropertyInterface;
use Oro\Bundle\GridBundle\Datagrid\RequestParameters;
use Oro\Bundle\GridBundle\Datagrid\PagerInterface;
use Oro\Bundle\GridBundle\EventDispatcher\ResultDatagridEvent;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class DatagridTest extends \PHPUnit_Framework_TestCase
{
    public function testAddProperty()
    {
        $property = $this->createProperty('test');
        $datagrid = $this->createDatagrid();

        $this->assertEquals(array(), $datagrid->getProperties());

        $datagrid->addProperty($property);

        $this->assertEquals(array('test' => $property), $datagrid->getProperties());
    }

    public function testGetProperties()
    {
        $property = $this->createProperty('property_name');
        $field = $this->createFieldDescription('field_name', $property);
        $datagrid = $this->createDatagrid(array('columns' => new FieldDescriptionCollection(array($field))));

        $this->assertEquals(array('property_name' => $property), $datagrid->getProperties());
    }

    public function testAddFilter()
    {
        $filterName = 'filter';
        $formType = 'text';
        $formOptions = array('disabled' => true);
        $filter = $this->createFilter($filterName, array($formType, $formOptions));

        $formBuilder = $this->getMockForAbstractClass('Symfony\Component\Form\Test\FormBuilderInterface');
        $datagrid = $this->createDatagrid(array('formBuilder' => $formBuilder));

        $this->assertAttributeEmpty('filters', $datagrid);
        $formBuilder->expects($this->once())->method('add')->with($filterName, $formType, $formOptions);
        $datagrid->addFilter($filter);
        $this->assertAttributeEquals(array('filter' => $filter), 'filters', $datagrid);
    }

    public function testGetFilters()
    {
        $datagrid = $this->createDatagrid();

        $expectedFilters = array(
            'filter_name_1' => $this->createFilter('filter_name_1'),
            'filter_name_2' => $this->createFilter('filter_name_2'),
        );

        foreach ($expectedFilters as $filter) {
            $datagrid->addFilter($filter);
        }

        $this->assertEquals($expectedFilters, $datagrid->getFilters());
    }

    public function testGetFilter()
    {
        $filterName = 'filter';
        $filter = $this->createFilter($filterName);
        $datagrid = $this->createDatagrid();

        $this->assertNull($datagrid->getFilter($filterName));
        $datagrid->addFilter($filter);
        $this->assertEquals($filter, $datagrid->getFilter($filterName));
    }

    public function testHasFilter()
    {
        $filterName = 'filter';
        $filter = $this->createFilter($filterName);
        $datagrid = $this->createDatagrid();

        $this->assertFalse($datagrid->hasFilter($filterName));
        $datagrid->addFilter($filter);
        $this->assertTrue($datagrid->hasFilter($filterName));
    }

    public function testRemoveFilter()
    {
        $filterName = 'filter';
        $filter = $this->createFilter($filterName);
        $datagrid = $this->createDatagrid();
        $datagrid->addFilter($filter);

        $this->assertTrue($datagrid->hasFilter($filterName));
        $datagrid->removeFilter($filterName);
        $this->assertFalse($datagrid->hasFilter($filterName));
    }

    public function hasActiveFiltersDataProvider()
    {
        return array(
            'has_active_filters' => array(
                '$sourceFilters' => array(
                    'filter_name_1' => false,
                    'filter_name_2' => true,
                ),
                '$isActive' => true,
            ),
            'has_not_active_filters' => array(
                '$sourceFilters' => array(
                    'filter_name_1' => false,
                    'filter_name_2' => false,
                ),
                '$isActive' => false,
            ),
        );
    }

    /**
     * @param array $sourceFilters
     * @param boolean $isActive
     * @dataProvider hasActiveFiltersDataProvider
     */
    public function testHasActiveFilters(array $sourceFilters, $isActive)
    {
        $datagrid = $this->createDatagrid();

        foreach ($sourceFilters as $filterName => $isActive) {
            $filter = $this->createFilter($filterName);
            $filter->expects($this->any())
                ->method('isActive')
                ->will($this->returnValue($isActive));
            $datagrid->addFilter($filter);
        }

        if ($isActive) {
            $this->assertTrue($datagrid->hasActiveFilters());
        } else {
            $this->assertFalse($datagrid->hasActiveFilters());
        }
    }

    public function testAddSorter()
    {
        $sorterName = 'sorter';
        $sorter = $this->createSorter($sorterName);
        $datagrid = $this->createDatagrid();

        $this->assertAttributeEmpty('sorters', $datagrid);
        $datagrid->addSorter($sorter);
        $this->assertAttributeEquals(array($sorterName => $sorter), 'sorters', $datagrid);
    }

    public function testGetSorters()
    {
        $datagrid = $this->createDatagrid();

        $expectedSorters = array(
            'sorter_name_1' => $this->createSorter('sorter_name_1'),
            'sorter_name_2' => $this->createSorter('sorter_name_2'),
        );

        foreach ($expectedSorters as $sorter) {
            $datagrid->addSorter($sorter);
        }

        $this->assertEquals($expectedSorters, $datagrid->getSorters());
    }

    public function testGetSorter()
    {
        $sorterName = 'sorter';
        $sorter = $this->createSorter($sorterName);
        $datagrid = $this->createDatagrid();

        $this->assertNull($datagrid->getSorter($sorterName));
        $datagrid->addSorter($sorter);
        $this->assertEquals($sorter, $datagrid->getSorter($sorterName));
    }

    public function testGetParameters()
    {
        $arrayParameters = array('test');
        $parameters = $this->createParameters($arrayParameters);
        $datagrid = $this->createDatagrid(array('parameters' => $parameters));
        $this->assertEquals($arrayParameters, $datagrid->getParameters());
    }

    public function testGetValues()
    {
        $arrayParameters = array('test');
        $parameters = $this->createParameters($arrayParameters);
        $datagrid = $this->createDatagrid(array('parameters' => $parameters));
        $this->assertEquals($arrayParameters, $datagrid->getValues());
    }

    public function testGetRouteGenerator()
    {
        $routeGenerator = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Route\RouteGeneratorInterface');
        $datagrid = $this->createDatagrid(array('routeGenerator' => $routeGenerator));
        $this->assertEquals($routeGenerator, $datagrid->getRouteGenerator());
    }

    public function testGetName()
    {
        $datagridName = 'datagrid';
        $datagrid = $this->createDatagrid(array('name' => $datagridName));
        $this->assertEquals($datagridName, $datagrid->getName());
    }

    public function testGetEntityHint()
    {
        $entityHint = 'Entity Hint';
        $datagrid = $this->createDatagrid(array('entityHint' => $entityHint));
        $this->assertEquals($entityHint, $datagrid->getEntityHint());
    }

    public function testAddRowAction()
    {
        $action = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Action\ActionInterface');
        $datagrid = $this->createDatagrid();

        $this->assertAttributeEmpty('rowActions', $datagrid);
        $datagrid->addRowAction($action);
        $this->assertAttributeEquals(array($action), 'rowActions', $datagrid);
    }

    public function testGetRowActions()
    {
        $datagrid = $this->createDatagrid();

        $expectedActions = array();
        for ($i = 0; $i < 5; $i++) {
            $actionMock = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Action\ActionInterface');
            $expectedActions[] = $actionMock;
            $datagrid->addRowAction($actionMock);
        }

        $this->assertEquals($expectedActions, $datagrid->getRowActions());
    }

    public function testSetValue()
    {
        // method is empty, do nothing
        $datagrid = $this->createDatagrid();
        $datagrid->setValue('name', '=', 'value');
    }

    public function testGetPager()
    {
        $perPage = 15;
        $page = 2;
        $request = new Request();

        $container = $this->getMockForAbstractClass(
            'Symfony\Component\DependencyInjection\ContainerInterface',
            array(),
            '',
            false,
            true,
            true,
            array('get')
        );
        $container->expects($this->any())
            ->method('get')
            ->with('request')
            ->will($this->returnValue($request));

        $parameters = new RequestParameters($container, 'datagrid_name');
        $parameters->set(
            ParametersInterface::PAGER_PARAMETERS,
            array('_page' => $page, '_per_page' => $perPage)
        );

        /** @var $pager PagerInterface */
        $pager = $this->getMock('Oro\Bundle\GridBundle\Datagrid\ORM\Pager', array('init'));
        $datagrid = $this->createDatagrid(array('pager' => $pager, 'parameters' => $parameters));

        $this->assertSame($pager, $datagrid->getPager());
        $this->assertEquals($page, $pager->getPage());
        $this->assertEquals($perPage, $pager->getMaxPerPage());
        $this->assertAttributeEquals(true, 'pagerApplied', $datagrid);
    }

    public function testGetQuery()
    {
        $query = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface');
        $datagrid = $this->createDatagrid(array('query' => $query));
        $this->assertSame($query, $datagrid->getQuery());
    }

    public function testGetColumns()
    {
        $columns = $this->getMock('Oro\Bundle\GridBundle\Field\FieldDescriptionCollection');
        $datagrid = $this->createDatagrid(array('columns' => $columns));

        $elements = array($this->createFieldDescription('test'));
        $columns->expects($this->once())->method('getElements')->will($this->returnValue($elements));
        $this->assertEquals($elements, $datagrid->getColumns());
    }

    public function testGetForm()
    {
        $form = $this->getMockForAbstractClass('Symfony\Component\Form\Test\FormInterface');
        $formBuilder = $this->getMockForAbstractClass('Symfony\Component\Form\Test\FormBuilderInterface');
        $filterParameters = array('filter' => 'value');
        $parameters = $this->createParameters(array(ParametersInterface::FILTER_PARAMETERS => $filterParameters));

        $datagrid = $this->createDatagrid(array('formBuilder' => $formBuilder, 'parameters' => $parameters));

        $formBuilder->expects($this->once())->method('getForm')->will($this->returnValue($form));
        $form->expects($this->once())->method('submit')->with($filterParameters);

        $this->assertEquals($form, $datagrid->getForm());
        $this->assertEquals($form, $datagrid->getForm()); // check form created once
    }

    public function getResultsDataProvider()
    {
        return array(
            array(
                'filters' => array(
                    array('name' => 'valid_filter', 'expectIsValid' => true, 'expectValue' => 'filter_value'),
                    array('name' => 'skip_filter', 'expectIsValid' => false),
                ),
                'sorters' => array(
                    array('name' => 'sorter_one', 'expectApply' => true, 'expectDirection' => 'ASC'),
                    array('name' => 'sorter_two', 'expectApply' => false),
                ),
                'pager' => array(
                    'expectPage' => 20,
                    'expectPerPage' => 25,
                ),
                'parametersData' => array(
                    ParametersInterface::FILTER_PARAMETERS
                        => array('valid_filter' => 'filter_value', 'skip_filter' => 'invalid value'),
                    ParametersInterface::SORT_PARAMETERS => array('sorter_one' => 'ASC'),
                    ParametersInterface::PAGER_PARAMETERS => array('_page' => 20, '_per_page' => 25),
                )
            )
        );
    }

    /**
     * @dataProvider getResultsDataProvider
     */
    public function testGetResults(
        array $filtersData,
        array $sortersData,
        array $pagerData,
        array $parametersData
    ) {
        $query = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface');
        $pager = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Datagrid\PagerInterface');

        $form = $this->getMockForAbstractClass('Symfony\Component\Form\Test\FormInterface');
        $formBuilder = $this->getMockForAbstractClass('Symfony\Component\Form\Test\FormBuilderInterface');
        $formBuilder->expects($this->once())->method('getForm')->will($this->returnValue($form));

        $eventDispatcher = $this->getMockForAbstractClass(
            'Symfony\Component\EventDispatcher\EventDispatcherInterface',
            array(),
            '',
            false,
            true,
            true,
            array('dispatch')
        );
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                ResultDatagridEvent::NAME,
                $this->isInstanceOf('Oro\Bundle\GridBundle\EventDispatcher\ResultDatagridEvent')
            );

        $parameters = $this->createParameters($parametersData);

        $datagrid = $this->createDatagrid(
            array(
                'query' => $query,
                'pager' => $pager,
                'formBuilder' => $formBuilder,
                'parameters' => $parameters,
                'eventDispatcher' => $eventDispatcher,
            )
        );

        $this->addFilterMocks($filtersData, $datagrid, $form, $query);
        $this->addSorterMocks($sortersData, $datagrid, $query);
        $this->addPagerMocks($pagerData, $pager);

        $queryResult = array(array('data'));
        $expectedResult = array(new ResultRecord($queryResult[0]));
        $query->expects($this->once())->method('execute')->will($this->returnValue($queryResult));
        $this->assertEquals($expectedResult, $datagrid->getResults());
    }

    /**
     * @param array $filtersData
     * @param Datagrid $datagrid
     * @param \PHPUnit_Framework_MockObject_MockObject $form
     * @param ProxyQueryInterface $query
     */
    private function addFilterMocks(
        array $filtersData,
        Datagrid $datagrid,
        \PHPUnit_Framework_MockObject_MockObject $form,
        ProxyQueryInterface $query
    ) {
        $filterFormChildrenValueMap = array();

        foreach ($filtersData as $data) {
            $name = $data['name'];
            $filter = $this->createFilter($name);
            $datagrid->addFilter($filter);

            $filterForm = $this->getMockForAbstractClass('Symfony\Component\Form\Test\FormInterface');
            $filterFormChildrenValueMap[] = array($name, $filterForm);
            $filterForm->expects($this->once())->method('isValid')->will($this->returnValue($data['expectIsValid']));

            if ($data['expectIsValid']) {
                $filterForm->expects($this->once())->method('getData')->will($this->returnValue($data['expectValue']));
                $filter->expects($this->once())->method('apply')->with($query, $data['expectValue']);
            } else {
                $filterForm->expects($this->never())->method('getData');
                $filter->expects($this->never())->method('apply');
            }
        }

        $form->expects($this->any())->method('get')->will($this->returnValueMap($filterFormChildrenValueMap));
    }

    /**
     * @param array $sortersData
     * @param Datagrid $datagrid
     * @param ProxyQueryInterface $query
     */
    private function addSorterMocks(
        array $sortersData,
        Datagrid $datagrid,
        ProxyQueryInterface $query
    ) {
        foreach ($sortersData as $data) {
            $name = $data['name'];
            $sorter = $this->createSorter($name);
            $datagrid->addSorter($sorter);

            if ($data['expectApply']) {
                $sorter->expects($this->once())->method('apply')->with($query, $data['expectDirection']);
            } else {
                $sorter->expects($this->never())->method('apply');
            }
        }
    }

    /**
     * @param array $pagerData,
     * @param \PHPUnit_Framework_MockObject_MockObject $pager
     */
    private function addPagerMocks(array $pagerData, $pager)
    {
        $pager->expects($this->once())->method('setPage')->with($pagerData['expectPage']);
        $pager->expects($this->once())->method('setMaxPerPage')->with($pagerData['expectPerPage']);
        $pager->expects($this->once())->method('init');
    }

    /**
     * Prepare all constructor argument mocks for datagrid and create
     *
     * @param array $arguments
     * @return Datagrid
     */
    private function createDatagrid($arguments = array())
    {
        $arguments = $this->getDatagridMockArguments($arguments);
        return new Datagrid(
            $arguments['query'],
            $arguments['columns'],
            $arguments['pager'],
            $arguments['formBuilder'],
            $arguments['routeGenerator'],
            $arguments['parameters'],
            $arguments['eventDispatcher'],
            $arguments['name'],
            $arguments['entityHint']
        );
    }

    private function getDatagridMockArguments(array $arguments = array())
    {
        $defaultArguments = array(
            'query'           => $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface'),
            'columns'         => $this->getMock('Oro\Bundle\GridBundle\Field\FieldDescriptionCollection'),
            'pager'           => $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Datagrid\PagerInterface'),
            'formBuilder'     => $this->getMockForAbstractClass('Symfony\Component\Form\Test\FormBuilderInterface'),
            'routeGenerator'  => $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Route\RouteGeneratorInterface'),
            'parameters'      => $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Datagrid\ParametersInterface'),
            'eventDispatcher' => $this->getMockForAbstractClass(
                'Symfony\Component\EventDispatcher\EventDispatcherInterface'
            ),
            'name'            => null,
            'entityHint'      => null,
        );
        return array_merge($defaultArguments, $arguments);
    }

    /**
     * @param string $name
     * @return PropertyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createProperty($name)
    {
        $result = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Property\PropertyInterface');
        $result->expects($this->any())->method('getName')->will($this->returnValue($name));
        return $result;
    }

    /**
     * @param $name
     * @param mixed $property
     * @return FieldDescriptionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createFieldDescription($name, $property = null)
    {
        if (!$property) {
            $property = $this->createProperty($name);
        }

        $result = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Field\FieldDescriptionInterface');
        $result->expects($this->any())->method('getName')->will($this->returnValue($name));
        $result->expects($this->any())->method('getProperty')->will($this->returnValue($property));

        return $result;
    }


    /**
     * @param string $name
     * @param array|null $renderSettings
     * @return FilterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createFilter($name, $renderSettings = null)
    {
        if (null === $renderSettings) {
            $renderSettings = array('text', array());
        }

        $result = $this->getMockBuilder('Oro\Bundle\GridBundle\Filter\FilterInterface')
            ->setMethods(array('getName', 'isActive', 'getFormName', 'apply', 'getRenderSettings'))
            ->getMockForAbstractClass();

        $result->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));

        $result->expects($this->any())
            ->method('getRenderSettings')
            ->will($this->returnValue($renderSettings));

        return $result;
    }

    /**
     * @param string $sorterName
     * @return SorterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createSorter($sorterName)
    {
        $result = $this->getMockBuilder('Oro\Bundle\GridBundle\Sorter\SorterInterface')
            ->setMethods(array('getName', 'apply'))
            ->getMockForAbstractClass();

        $result->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($sorterName));

        return $result;
    }

    /**
     * @param array $parameters
     * @return ParametersInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createParameters(array $parameters = array())
    {
        $result = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Datagrid\ParametersInterface');

        $result->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue($parameters));

        $result->expects($this->any())
            ->method('get')
            ->will(
                $this->returnCallback(
                    function ($key) use ($parameters) {
                        return isset($parameters[$key]) ? $parameters[$key] : null;
                    }
                )
            );

        return $result;
    }

    public function testCreateView()
    {
        $datagrid = $this->createDatagrid();
        $datagridView = $datagrid->createView();

        $this->assertInstanceOf('Oro\Bundle\GridBundle\Datagrid\DatagridView', $datagridView);
        $this->assertEquals($datagrid, $datagridView->getDatagrid());
    }
}
