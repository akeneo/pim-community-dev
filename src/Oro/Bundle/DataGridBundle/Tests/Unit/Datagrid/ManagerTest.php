<?php

namespace Oro\Bundle\DataGridBundle\Tests\Unit\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Provider\ChainConfigurationProvider;
use Oro\Bundle\DataGridBundle\Provider\ConfigurationProvider;
use Oro\Bundle\DataGridBundle\Provider\SystemAwareResolver;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_NAME = 'testGrid';

    /** @var Manager */
    protected $manager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $builder;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $resolver;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $requestParams;

    /** @var array */
    protected $testConfiguration = [
        self::TEST_NAME => [
            'someKey'        => [],
            'someAnotherKey' => []
        ]
    ];

    public function setUp(): void
    {
        $this->builder = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()->getMock();

        $this->resolver = $this->getMockBuilder(SystemAwareResolver::class)
            ->disableOriginalConstructor()->getMock();

        $this->requestParams = $this->getMockBuilder(RequestParameters::class)
            ->disableOriginalConstructor()->getMock();

        $configProvider = new ConfigurationProvider($this->testConfiguration, $this->resolver);
        $chainConfigProvider = new ChainConfigurationProvider();
        $chainConfigProvider->addProvider($configProvider);
        $this->manager = new Manager($chainConfigProvider, $this->builder, $this->requestParams);
    }

    public function tearDown()
    {
        unset($this->builder);
        unset($this->resolver);
        unset($this->requestParams);
        unset($this->manager);
    }

    /**
     * @dataProvider datagridProvider
     */
    public function testGetDataGrid($name, $expectedExceptionMessage)
    {
        if ($expectedExceptionMessage) {
            $this->setExpectedException('\RuntimeException', $expectedExceptionMessage);
        } else {
            $this->resolver->expects($this->once())->method('resolve')->will($this->returnArgument(1));

            $configurationClass = DatagridConfiguration::class;
            $this->builder->expects($this->once())->method('build')
                ->with($this->isInstanceOf($configurationClass));
            $this->requestParams->expects($this->once())->method('setRootParameter')->with($this->equalTo($name));
        }
        $this->manager->getDatagrid($name);
    }

    /**
     * @return array
     */
    public function datagridProvider()
    {
        return [
            'test existing grid configuration'                   => [
                self::TEST_NAME,
                false
            ],
            'test some not existing grid should throw exception' => [
                'someName',
                'A configuration for "someName" datagrid was not found.'
            ]
        ];
    }

    /**
     * @dataProvider datagridConfigurationProvider
     */
    public function testGetDatagridConfiguration($names, $expectedResolverCall, $expectedException)
    {
        if ($expectedException) {
            $this->setExpectedException('\RuntimeException');
        }
        $this->resolver->expects($this->exactly($expectedResolverCall))->method('resolve')
            ->will($this->returnArgument(1));

        foreach ($names as $name) {
            $result = $this->manager->getConfigurationForGrid($name);
            $this->assertInstanceOf(DatagridConfiguration::class, $result);
            $this->assertEquals($name, $result->getName());
        }
    }

    /**
     * @return array
     */
    public function datagridConfigurationProvider()
    {
        return [
            'call once, should call resolver'             => [
                [self::TEST_NAME],
                1,
                false
            ],
            'twice called grid, should be processed once' => [
                [self::TEST_NAME, self::TEST_NAME],
                1,
                false
            ],
            'test some not existing grid should throw exception' => [
                ['SomeNotExistingName'],
                0,
                true
            ]
        ];
    }
}
