<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\EntityAutocomplete;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\FormBundle\EntityAutocomplete\ServiceSearchFactory;
use Oro\Bundle\FormBundle\EntityAutocomplete\SearchHandlerInterface;

use Oro\Bundle\FormBundle\Tests\Unit\MockHelper;

class QueryBuilderSearchFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $container;

    /**
     * @var ServiceSearchFactory
     */
    protected $factory;

    protected function setUp()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->factory = new ServiceSearchFactory($this->container);
    }

    public function testCreate()
    {
        $searchHandler = $this->getMock('Oro\Bundle\FormBundle\EntityAutocomplete\SearchHandlerInterface');

        $this->container->expects($this->once())
            ->method('get')
            ->with('foo_service')
            ->will($this->returnValue($searchHandler));

        $this->assertSame(
            $searchHandler,
            $this->factory->create(array('options' => array('service' => 'foo_service')))
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
        MockHelper::addMockExpectedCalls($this->container, $expectContainerCalls, $this);
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
                'Option "options.service" is required'
            ),
            'service not valid' => array(
                'options' => array(
                    'options' => array(
                        'service' => 'foo_service'
                    )
                ),
                'expectContainerCalls' => array(
                    array('get', array('foo_service'), new \stdClass()),
                ),
                'RuntimeException',
                'Service "foo_service" must be an instance of'
                    . ' Oro\\Bundle\\FormBundle\\EntityAutocomplete\\SearchHandlerInterface'
            )
        );
    }
}
