<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Datagrid;

use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\GridBundle\Datagrid\RequestParameters;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;

use Oro\Bundle\GridBundle\Tests\Unit\Datagrid\Stub\StubDatagridManager;

class DatagridManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test parameters
     */
    const TEST_NAME                      = 'test_name';
    const TEST_HINT                      = 'test_hint';
    const TEST_FILTERABLE_SORTABLE_FIELD = 'test_filterable_sortable_field';
    const TEST_SORTABLE_FIELD            = 'test_sortable_field';
    const TEST_DOMAIN                    = 'someDomain';
    const TEST_IDENTIFIER                = 'some_id';
    const TEST_ALIAS                     = 'some_alias';

    /**
     * @var DatagridManager
     */
    protected $model;

    /**
     * @var array
     */
    protected $testFields = array(
        self::TEST_SORTABLE_FIELD => array(
            'option_2' => 'value_2',
            'sortable' => true
        ),
        self::TEST_FILTERABLE_SORTABLE_FIELD => array(
            'option_3'   => 'value_3',
            'filterable' => 'true',
            'sortable'   => true
        ),
        'simple_field' => array(
            'option_4' => 'value_4'
        )
    );

    protected $testProperties = array();

    /**
     * @var array
     */
    protected $testRowActions = array(
        1 => array('row_1' => 'parameter_1'),
        2 => array('row_2' => 'parameter_2'),
    );

    protected function setUp()
    {
        $this->testFields = $this->createFieldDescriptions($this->testFields);

        $this->testProperties = array(
            $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Property\PropertyInterface')
        );
        $this->model = new StubDatagridManager($this->testFields, $this->testProperties, $this->testRowActions);
    }

    protected function createFieldDescriptions(array $fieldsOptions)
    {
        $result = array();
        // convert fields to field descriptions
        foreach ($fieldsOptions as $fieldName => $fieldOptions) {
            if (is_array($fieldOptions)) {
                $field = new FieldDescription();
                $field->setName($fieldName);
                $field->setOptions($fieldOptions);
                $result[$fieldName] = $field;
            }
        }
        return $result;
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    public function testSetDatagridBuilder()
    {
        $datagridBuilderMock = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Builder\DatagridBuilderInterface');

        $this->assertAttributeEmpty('datagridBuilder', $this->model);
        $this->model->setDatagridBuilder($datagridBuilderMock);
        $this->assertAttributeEquals($datagridBuilderMock, 'datagridBuilder', $this->model);
    }

    public function testSetListBuilder()
    {
        $listBuilderMock = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Builder\ListBuilderInterface');

        $this->assertAttributeEmpty('listBuilder', $this->model);
        $this->model->setListBuilder($listBuilderMock);
        $this->assertAttributeEquals($listBuilderMock, 'listBuilder', $this->model);
    }

    public function testSetQueryFactory()
    {
        $queryFactoryMock = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Datagrid\QueryFactoryInterface');

        $this->assertAttributeEmpty('queryFactory', $this->model);
        $this->model->setQueryFactory($queryFactoryMock);
        $this->assertAttributeEquals($queryFactoryMock, 'queryFactory', $this->model);
    }

    public function testSetTranslator()
    {
        $translatorMock = $this->getMockForAbstractClass('Symfony\Component\Translation\TranslatorInterface');

        $this->assertAttributeEmpty('translator', $this->model);
        $this->model->setTranslator($translatorMock);
        $this->assertAttributeEquals($translatorMock, 'translator', $this->model);
    }

    public function testSetValidator()
    {
        $validatorMock = $this->getMockForAbstractClass('Symfony\Component\Validator\ValidatorInterface');

        $this->assertAttributeEmpty('validator', $this->model);
        $this->model->setValidator($validatorMock);
        $this->assertAttributeEquals($validatorMock, 'validator', $this->model);
    }

    public function testSetRouter()
    {
        $routerMock = $this->getMockBuilder('Symfony\Component\Routing\Router')
            ->disableOriginalConstructor()->getMock();

        $this->assertAttributeEmpty('router', $this->model);
        $this->model->setRouter($routerMock);
        $this->assertAttributeEquals($routerMock, 'router', $this->model);
    }

    public function testSetRouteGenerator()
    {
        $routeGeneratorMock = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Route\RouteGeneratorInterface');

        $this->assertAttributeEmpty('routeGenerator', $this->model);
        $this->model->setRouteGenerator($routeGeneratorMock);
        $this->assertAttributeEquals($routeGeneratorMock, 'routeGenerator', $this->model);
    }

    public function testSetParameters()
    {
        $parametersMock = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Datagrid\ParametersInterface');

        $this->assertAttributeEmpty('parameters', $this->model);
        $this->model->setParameters($parametersMock);
        $this->assertAttributeEquals($parametersMock, 'parameters', $this->model);
    }

    public function testSetName()
    {
        $this->assertAttributeEmpty('name', $this->model);
        $this->model->setName(self::TEST_NAME);
        $this->assertAttributeEquals(self::TEST_NAME, 'name', $this->model);
    }

    public function testSetTranslationDomain()
    {
        $this->assertAttributeEmpty('translationDomain', $this->model);
        $this->model->setTranslationDomain(self::TEST_DOMAIN);
        $this->assertAttributeEquals(self::TEST_DOMAIN, 'translationDomain', $this->model);
    }

    public function testSetIdentifierField()
    {
        $this->assertAttributeEmpty('identifierField', $this->model);
        $this->model->setIdentifierField(self::TEST_IDENTIFIER);
        $this->assertAttributeEquals(self::TEST_IDENTIFIER, 'identifierField', $this->model);
    }

    public function testSetEntityHint()
    {
        $this->assertAttributeEmpty('entityHint', $this->model);
        $this->model->setEntityHint(self::TEST_HINT);
        $this->assertAttributeEquals(self::TEST_HINT, 'entityHint', $this->model);
    }

    public function testGetDatagrid()
    {
        $datagridMock       = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Datagrid\DatagridInterface');
        $queryMock          = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface');
        $routeGeneratorMock = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Route\RouteGeneratorInterface');
        $parameters         = $this->createTestParameters();

        $listCollection = new FieldDescriptionCollection();

        $listBuilderMock = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Builder\ListBuilderInterface',
            array(),
            '',
            false,
            true,
            true,
            array('getBaseList')
        );
        $listBuilderMock->expects($this->once())
            ->method('getBaseList')
            ->will($this->returnValue($listCollection));

        $queryFactoryMock = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory\EntityQueryFactory',
            array(),
            '',
            false,
            true,
            true,
            array('createQuery', 'getAlias')
        );
        $queryFactoryMock->expects($this->once())
            ->method('createQuery')
            ->will($this->returnValue($queryMock));
        $queryFactoryMock->expects($this->any())
            ->method('getAlias')->will($this->returnValue(self::TEST_ALIAS));

        $datagridBuilderMock = $this->getMockBuilder('Oro\Bundle\GridBundle\Builder\DatagridBuilderInterface')
            ->setMethods(array('getDatagrid', 'addFilter', 'addSorter', 'addRowAction'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $datagridBuilderMock->expects($this->at(0))
            ->method('getBaseDatagrid')
            ->with(
                $queryMock,
                $listCollection,
                $routeGeneratorMock,
                $parameters,
                self::TEST_NAME
            )
            ->will($this->returnValue($datagridMock));
        $datagridBuilderMock->expects($this->at(1))
            ->method('addProperty')
            ->with($datagridMock, $this->testProperties[0]);

        $datagridBuilderMock->expects($this->at(2))
            ->method('addFilter')
            ->with($datagridMock, $this->testFields[self::TEST_FILTERABLE_SORTABLE_FIELD]);

        $datagridBuilderMock->expects($this->at(3))
            ->method('addSorter')
            ->with($datagridMock, $this->testFields[self::TEST_SORTABLE_FIELD]);

        $datagridBuilderMock->expects($this->at(4))
            ->method('addSorter')
            ->with($datagridMock, $this->testFields[self::TEST_FILTERABLE_SORTABLE_FIELD]);

        $datagridBuilderMock->expects($this->at(5))
            ->method('addRowAction')
            ->with($datagridMock, $this->testRowActions[1]);

        $datagridBuilderMock->expects($this->at(6))
            ->method('addRowAction')
            ->with($datagridMock, $this->testRowActions[2]);

        $this->model->setDatagridBuilder($datagridBuilderMock);
        $this->model->setListBuilder($listBuilderMock);
        $this->model->setQueryFactory($queryFactoryMock);
        $this->model->setRouteGenerator($routeGeneratorMock);
        $this->model->setParameters($parameters);
        $this->model->setName(self::TEST_NAME);
        $this->model->setEntityHint(self::TEST_HINT);
        $this->model->setIdentifierField(self::TEST_IDENTIFIER);

        $translatorMock = $this->getMockForAbstractClass('Symfony\Component\Translation\TranslatorInterface');
        $this->model->setTranslator($translatorMock);
        $translatorMock->expects($this->at(0))->method('trans')
            ->with(self::TEST_IDENTIFIER, array(), null)
            ->will(
                $this->returnCallback(
                    function ($string, $params, $domain) {
                        return 'trans_' . $string;
                    }
                )
            );


        $this->assertEquals($datagridMock, $this->model->getDatagrid());

        $idField = $this->createFieldDescriptions(
            array(
                self::TEST_IDENTIFIER => array(
                    'field_name'   => self::TEST_IDENTIFIER,
                    'type'         => FieldDescriptionInterface::TYPE_INTEGER,
                    'entity_alias' => self::TEST_ALIAS,
                    'label'        => 'trans_' . self::TEST_IDENTIFIER,
                    'filter_type'  => FilterInterface::TYPE_NUMBER,
                    'show_column'  => false
                )
            )
        );
        $this->testFields = array_merge($this->testFields, $idField);

        $this->assertEquals($this->testFields, $listCollection->getElements());

        $defaultParameters = array(
            self::TEST_NAME => array(
                ParametersInterface::SORT_PARAMETERS => array(
                    self::TEST_SORTABLE_FIELD => SorterInterface::DIRECTION_ASC
                )
            )
        );
        $this->assertEquals($defaultParameters, $parameters->toArray());
    }

    protected function createTestParameters()
    {
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

        return new RequestParameters($container, self::TEST_NAME);
    }

    public function testGetRouteGenerator()
    {
        $routeGenerator = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Route\RouteGeneratorInterface');
        $this->model->setRouteGenerator($routeGenerator);
        $this->assertEquals($routeGenerator, $this->model->getRouteGenerator());
    }
}
