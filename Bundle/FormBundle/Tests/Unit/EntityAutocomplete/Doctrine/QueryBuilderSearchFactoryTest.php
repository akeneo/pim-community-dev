<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\EntityAutocomplete\Transformer;

use Doctrine\ORM\QueryBuilder;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\FormBundle\EntityAutocomplete\Property;
use Oro\Bundle\FormBundle\EntityAutocomplete\Doctrine\QueryBuilderSearchHandler;
use Oro\Bundle\FormBundle\EntityAutocomplete\Doctrine\QueryBuilderSearchFactory;

class QueryBuilderSearchFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $container;

    /**
     * @var QueryBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $queryBuilder;

    /**
     * @var QueryBuilderSearchFactory
     */
    protected $factory;

    protected function setUp()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('getRootAliases'))
            ->getMock();
        $this->factory = new QueryBuilderSearchFactory($this->container);
    }

    /**
     * @dataProvider createDataProvider
     * @param array $options
     * @param array $expectContainerCalls
     * @param array $expectQueryBuilderCalls
     * @param array $expectedAttributes
     */
    public function testCreate(
        array $options,
        array $expectContainerCalls,
        array $expectQueryBuilderCalls,
        array $expectedAttributes
    ) {
        $this->addMockExpectedCalls($this->container, $expectContainerCalls);
        $this->addMockExpectedCalls($this->queryBuilder, $expectQueryBuilderCalls);

        $searchHandler = $this->factory->create($options);

        $this->assertInstanceOf(
            'Oro\Bundle\FormBundle\EntityAutocomplete\Doctrine\QueryBuilderSearchHandler',
            $searchHandler
        );

        foreach ($expectedAttributes as $attributeName => $expectedValue) {
            if (is_string($expectedValue) && method_exists($this, $expectedValue)) {
                $expectedValue = $this->$expectedValue();
            }
            $this->assertAttributeEquals($expectedValue, $attributeName, $searchHandler);
        }
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        return array(
            'without entity alias' => array(
                'options' => array(
                    'properties' => array($this->createProperty('foo')),
                    'options' => array(
                        'query_builder_service' => 'foo_query_builder'
                    )
                ),
                'expectContainerCalls' => array(
                    array('get', array('foo_query_builder'), 'getMockQueryBuilder'),
                ),
                'expectQueryBuilderCalls' => array(
                    array('getRootAliases', array(), array('root_entity_alias')),
                ),
                'expectedAttributes' => array(
                    'queryBuilder' => 'getMockQueryBuilder',
                    'properties' => array($this->createProperty('foo')),
                    'entityAlias' => 'root_entity_alias'
                )
            ),
            'with entity alias' => array(
                'options' => array(
                    'properties' => array($this->createProperty('foo')),
                    'options' => array(
                        'query_builder_service' => 'foo_query_builder',
                        'query_entity_alias' => 'foo_entity_alias'
                    )
                ),
                'expectContainerCalls' => array(
                    array('get', array('foo_query_builder'), 'getMockQueryBuilder'),
                ),
                'expectQueryBuilderCalls' => array(),
                'expectedAttributes' => array(
                    'queryBuilder' => 'getMockQueryBuilder',
                    'properties' => array($this->createProperty('foo')),
                    'entityAlias' => 'foo_entity_alias'
                )
            )
        );
    }

    /**
     * @dataProvider createFailsDataProvider
     * @param array $options
     * @param array $expectContainerCalls
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     */
    public function testCreateFails(
        array $options,
        array $expectContainerCalls,
        $expectedException,
        $expectedExceptionMessage
    ) {
        $this->addMockExpectedCalls($this->container, $expectContainerCalls);
        $this->setExpectedException($expectedException, $expectedExceptionMessage);
        $this->factory->create($options);
    }

    /**
     * @return array
     */
    public function createFailsDataProvider()
    {
        return array(
            'properties required' => array(
                'options' => array(),
                'expectContainerCalls' => array(),
                'RuntimeException',
                'Option "properties" is required'
            ),
            'query_builder_service required' => array(
                'options' => array(
                    'properties' => array($this->createProperty('foo'))
                ),
                'expectContainerCalls' => array(),
                'RuntimeException',
                'Option "options.query_builder_service" is required'
            ),
            'query_builder_service not valid' => array(
                'options' => array(
                    'properties' => array($this->createProperty('foo')),
                    'options' => array(
                        'query_builder_service' => 'foo_query_builder'
                    )
                ),
                'expectContainerCalls' => array(
                    array('get', array('foo_query_builder'), new \stdClass()),
                ),
                'RuntimeException',
                'Service "foo_query_builder" must be an instance of Doctrine\ORM\QueryBuilder'
            )
        );
    }

    protected function getMockQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * @param string $name
     * @return Property
     */
    protected function createProperty($name)
    {
        return new Property(array('name' => $name));
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param array $expectedCalls
     */
    private function addMockExpectedCalls($mock, array $expectedCalls)
    {
        $index = 0;
        if ($expectedCalls) {
            foreach ($expectedCalls as $expectedCall) {
                list($method, $arguments, $result) = $expectedCall;

                $methodExpectation = $mock->expects($this->at($index++))->method($method);
                $methodExpectation = call_user_func_array(array($methodExpectation, 'with'), $arguments);
                if (is_string($result) && method_exists($this, $result)) {
                    $result = $this->$result();
                }
                $methodExpectation->will($this->returnValue($result));
            }
        } else {
            $mock->expects($this->never())->method($this->anything());
        }
    }
}
