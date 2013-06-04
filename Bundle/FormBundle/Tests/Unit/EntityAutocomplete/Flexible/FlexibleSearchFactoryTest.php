<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\EntityAutocomplete\Flexible;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;

use Oro\Bundle\FormBundle\EntityAutocomplete\Property;
use Oro\Bundle\FormBundle\EntityAutocomplete\Flexible\FlexibleSearchFactory;
use Oro\Bundle\FormBundle\EntityAutocomplete\Flexible\FlexibleSearchHandler;

use Oro\Bundle\FormBundle\Tests\Unit\MockHelper;

class FlexibleSearchFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FlexibleManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $managerRegistry;

    /**
     * @var FlexibleManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $flexibleManager;

    /**
     * @var FlexibleEntityRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $flexibleRepository;

    /**
     * @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $container;

    /**
     * @var FlexibleSearchFactory
     */
    protected $factory;

    protected function setUp()
    {
        $this->managerRegistry =
            $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry')
                ->disableOriginalConstructor()
                ->setMethods(array('getManager'))
                ->getMock();

        $this->flexibleManager = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getFlexibleRepository'))
            ->getMock();

        $this->flexibleRepository =
            $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository')
                ->disableOriginalConstructor()
                ->setMethods(array('getFlexibleRepository'))
                ->getMock();

        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $this->factory = new FlexibleSearchFactory($this->container, $this->managerRegistry);
    }

    /**
     * @dataProvider createDataProvider
     * @param array $options
     * @param array $expectContainerCalls
     * @param array $expectManagerRegistryCalls
     * @param array $expectedAttributes
     */
    public function testCreate(
        array $options,
        array $expectContainerCalls,
        array $expectManagerRegistryCalls,
        array $expectFlexibleManagerCalls,
        array $expectedAttributes
    ) {
        MockHelper::addMockExpectedCalls($this->container, $expectContainerCalls, $this);
        MockHelper::addMockExpectedCalls($this->managerRegistry, $expectManagerRegistryCalls, $this);
        MockHelper::addMockExpectedCalls($this->flexibleManager, $expectFlexibleManagerCalls, $this);

        $searchHandler = $this->factory->create($options);

        $this->assertInstanceOf(
            'Oro\Bundle\FormBundle\EntityAutocomplete\Flexible\FlexibleSearchHandler',
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
            'with entity_class' => array(
                'options' => array(
                    'properties' => array($this->createProperty('foo')),
                    'entity_class' => 'FooClassName'
                ),
                'expectContainerCalls' => array(),
                'expectManagerRegistryCalls' => array(
                    array('getManager', array('FooClassName'), 'getMockFlexibleManager'),
                ),
                'expectFlexibleManagerCalls' => array(
                    array('getFlexibleRepository', array(), 'getMockFlexibleRepository'),
                ),
                'expectedAttributes' => array(
                    'repository' => 'getMockFlexibleRepository',
                    'properties' => array($this->createProperty('foo')),
                )
            ),
            'with flexible_manager' => array(
                'options' => array(
                    'properties' => array($this->createProperty('foo')),
                    'options' => array(
                        'flexible_manager' => 'foo_flexible_manager'
                    )
                ),
                'expectContainerCalls' => array(
                    array('get', array('foo_flexible_manager'), 'getMockFlexibleManager'),
                ),
                'expectManagerRegistryCalls' => array(),
                'expectFlexibleManagerCalls' => array(
                    array('getFlexibleRepository', array(), 'getMockFlexibleRepository'),
                ),
                'expectedAttributes' => array(
                    'repository' => 'getMockFlexibleRepository',
                    'properties' => array($this->createProperty('foo')),
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
                'Option "properties" is required'
            ),
            'entity_class required' => array(
                'options' => array(
                    'properties' => array($this->createProperty('foo'))
                ),
                'expectContainerCalls' => array(),
                'RuntimeException',
                'Option "entity_class" is required'
            ),
            'flexible_manager is invalid' => array(
                'options' => array(
                    'properties' => array($this->createProperty('foo')),
                    'options' => array(
                        'flexible_manager' => 'foo_flexible_manager'
                    )
                ),
                'expectContainerCalls' => array(
                    array('get', array('foo_flexible_manager'), new \stdClass())
                ),
                'RuntimeException',
                'Service "foo_flexible_manager" must be an instance of '
                    . 'Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager'
            )
        );
    }

    /**
     * @return FlexibleManager|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockFlexibleManager()
    {
        return $this->flexibleManager;
    }

    /**
     * @return FlexibleEntityRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockFlexibleRepository()
    {
        return $this->flexibleRepository;
    }

    /**
     * @param string $name
     * @return Property
     */
    protected function createProperty($name)
    {
        return new Property(array('name' => $name));
    }
}
