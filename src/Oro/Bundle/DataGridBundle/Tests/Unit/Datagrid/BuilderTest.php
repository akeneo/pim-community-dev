<?php

namespace Oro\Bundle\DataGridBundle\Tests\Unit\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Datagrid;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Extension\Acceptor;
use Oro\Bundle\DataGridBundle\Extension\ExtensionVisitorInterface;
use Oro\Bundle\DataGridBundle\Tests\Unit\DataFixtures\Stub\DatagridClass;
use Oro\Bundle\SecurityBundle\SecurityFacade;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    const TEST_DATASOURCE_TYPE = 'array';
    const TEST_DATAGRID_NAME = 'testGrid';
    const TEST_ACL_NAME = 'testACL';
    const TEST_ACL_DESCRIPTOR = 'testACLDescriptor';

    const DEFAULT_DATAGRID_CLASS = Datagrid::class;
    const DEFAULT_ACCEPTOR_CLASS = Acceptor::class;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Builder */
    protected $builder;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $eventDispatcher;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $securityFacade;

    public function setUp(): void
    {
        $this->eventDispatcher = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcher');
        $this->securityFacade = $this->getMockBuilder(SecurityFacade::class)
            ->disableOriginalConstructor()->getMock();
    }

    public function tearDown()
    {
        unset($this->eventDispatcher);
        unset($this->securityFacade);
    }

    public function testRegisterExtensions()
    {
        $builder = $this->getBuilderMock();
        $extMock = $this->getMockForAbstractClass(ExtensionVisitorInterface::class);
        $ext2Mock = clone $extMock;
        $ext3Mock = clone $extMock;

        $builder->registerExtension($extMock);
        $builder->registerExtension($ext2Mock);

        $this->assertAttributeContains($extMock, 'extensions', $builder);
        $this->assertAttributeContains($ext2Mock, 'extensions', $builder);
        $this->assertAttributeNotContains($ext3Mock, 'extensions', $builder);
    }

    public function testRegisterDatasource()
    {
        $builder = $this->getBuilderMock();
        $datasourceMock = $this->getMockForAbstractClass(DatasourceInterface::class);

        $builder->registerDatasource(self::TEST_DATASOURCE_TYPE, $datasourceMock);

        $this->assertAttributeContains($datasourceMock, 'dataSources', $builder);
        $this->assertAttributeCount(1, 'dataSources', $builder);
    }

    /**
     * @dataProvider buildProvider
     *
     * @param DatagridConfiguration $config
     * @param string                $resultFQCN
     * @param array                 $raisedEvents
     * @param int                   $extensionsCount
     * @param array                 $extensionsMocks
     */
    public function testBuild($config, $resultFQCN, $raisedEvents, $extensionsCount, $extensionsMocks = [])
    {
        $builder = $this->getBuilderMock(['buildDataSource']);

        foreach ($extensionsMocks as $extension) {
            $builder->registerExtension($extension);
        }

        foreach ($raisedEvents as $at => $eventDetails) {
            list($name, $eventType) = $eventDetails;
            $this->eventDispatcher->expects($this->at($at))->method('dispatch')
                ->with($this->equalTo($name), $this->isInstanceOf($eventType));
        }

        /** @var DatagridInterface $result */
        $result = $builder->build($config);
        $this->assertInstanceOf($resultFQCN, $result);

        $this->assertInstanceOf(self::DEFAULT_ACCEPTOR_CLASS, $result->getAcceptor());

        $this->assertCount($extensionsCount, $result->getAcceptor()->getExtensions());
    }

    /**
     * @return array
     */
    public function buildProvider()
    {
        $stubDatagridClass = DatagridClass::class;
        $baseEventList = [
            ['oro_datagrid.datgrid.build.before', BuildBefore::class],
            [
                sprintf('oro_datagrid.datgrid.build.before.%s', self::TEST_DATAGRID_NAME),
                BuildBefore::class
            ],
            ['oro_datagrid.datgrid.build.after', BuildAfter::class],
            [
                sprintf('oro_datagrid.datgrid.build.after.%s', self::TEST_DATAGRID_NAME),
                BuildAfter::class
            ],
        ];

        $extToAdd = $this->getMockForAbstractClass(ExtensionVisitorInterface::class);
        $extToAdd2 = $this->getMockForAbstractClass(ExtensionVisitorInterface::class);
        $extNotToAdd = $this->getMockForAbstractClass(ExtensionVisitorInterface::class);

        $extToAdd->expects($this->any())->method('isApplicable')->will($this->returnValue(true));
        $extToAdd2->expects($this->any())->method('isApplicable')->will($this->returnValue(true));
        $extNotToAdd->expects($this->any())->method('isApplicable')->will($this->returnValue(false));

        return [
            'Base datagrid should be created without extensions'         => [
                DatagridConfiguration::createNamed(self::TEST_DATAGRID_NAME, []),
                self::DEFAULT_DATAGRID_CLASS,
                $baseEventList,
                0
            ],
            'Datagrid should be created as object type passed in config' => [
                DatagridConfiguration::createNamed(
                    self::TEST_DATAGRID_NAME,
                    ['options' => ['base_datagrid_class' => $stubDatagridClass]]
                ),
                $stubDatagridClass,
                $baseEventList,
                0
            ],
            'Extension passed check'                                     => [
                DatagridConfiguration::createNamed(self::TEST_DATAGRID_NAME, []),
                self::DEFAULT_DATAGRID_CLASS,
                $baseEventList,
                1,
                [$extToAdd, $extNotToAdd]
            ],
            'Both extensions passed check'                               => [
                DatagridConfiguration::createNamed(self::TEST_DATAGRID_NAME, []),
                self::DEFAULT_DATAGRID_CLASS,
                $baseEventList,
                2,
                [$extToAdd, $extNotToAdd, $extToAdd2]
            ]
        ];
    }

    /**
     * @dataProvider buildDatasourceProvider
     *
     * @param  DatagridConfiguration $config
     * @param array                  $datasources
     * @param array                  $expectedACLCheck
     * @param array                  $expectedException
     * @param int                    $processCallExpects
     */
    public function testBuildDatasource(
        $config,
        $datasources = [],
        array $expectedACLCheck = null,
        array $expectedException = null,
        $processCallExpects = 0
    ) {
        $builder = $this->getBuilderMock(['isResourceGranted']);
        $grid = $this->getMockForAbstractClass(DatagridInterface::class);

        foreach ($datasources as $type => $obj) {
            $builder->registerDatasource($type, $obj);
            if ($processCallExpects) {
                $obj->expects($this->once())->method('process')->with($grid);
            }
        }

        if ($expectedACLCheck !== null) {
            list($name, $result) = $expectedACLCheck;

            $builder->expects($this->once())->method('isResourceGranted')->with($this->equalTo($name))
                ->will($this->returnValue($result));
        }

        if ($expectedException !== null) {
            list($name, $message) = $expectedException;

            $this->setExpectedException($name, $message);
        }

        $method = new \ReflectionMethod($builder, 'buildDataSource');
        $method->setAccessible(true);
        $method->invoke($builder, $grid, $config);
    }

    /**
     * @return array
     */
    public function buildDatasourceProvider()
    {
        $datasourceMock = $this->getMockForAbstractClass(DatasourceInterface::class);
        return [
            'Datasource not configured, exceptions should be thrown' => [
                DatagridConfiguration::create([]),
                [],
                null,
                ['\RuntimeException', 'Datagrid source does not configured']
            ],
            'Configured datasource does not exist'                   => [
                DatagridConfiguration::create(['source' => ['type' => self::TEST_DATASOURCE_TYPE]]),
                [],
                null,
                ['\RuntimeException', sprintf('Datagrid source "%s" does not exist', self::TEST_DATASOURCE_TYPE)]
            ],
            'Configured datasource denied for caller'                => [
                DatagridConfiguration::create(
                    ['source' => ['type' => self::TEST_DATASOURCE_TYPE, 'acl_resource' => self::TEST_ACL_NAME]]
                ),
                [self::TEST_DATASOURCE_TYPE => clone $datasourceMock],
                [self::TEST_ACL_NAME, false],
                ['Symfony\Component\Security\Core\Exception\AccessDeniedException', 'Access denied.']
            ],
            'Configured correct and allowed'                         => [
                DatagridConfiguration::create(
                    ['source' => ['type' => self::TEST_DATASOURCE_TYPE, 'acl_resource' => self::TEST_ACL_NAME]]
                ),
                [self::TEST_DATASOURCE_TYPE => clone $datasourceMock],
                [self::TEST_ACL_NAME, true],
                null,
                true
            ],
            'Configured correct and ACL not set'                     => [
                DatagridConfiguration::create(
                    ['source' => ['type' => self::TEST_DATASOURCE_TYPE]]
                ),
                [self::TEST_DATASOURCE_TYPE => clone $datasourceMock],
                null,
                null,
                true
            ]
        ];
    }

    /**
     * @dataProvider isGrantedProvider
     *
     * @param string $acl
     * @param bool   $withDelimiter
     */
    public function testIsGranted($acl, $withDelimiter)
    {
        $builder = $this->getBuilderMock();

        if ($withDelimiter) {
            $this->securityFacade->expects($this->once())->method('isGranted')->with(
                self::TEST_ACL_NAME,
                self::TEST_ACL_DESCRIPTOR
            );
        } else {
            $this->securityFacade->expects($this->once())->method('isGranted')->with(self::TEST_ACL_NAME);
        }

        $method = new \ReflectionMethod($builder, 'isResourceGranted');
        $method->setAccessible(true);
        $method->invoke($builder, $acl);
    }

    /**
     * @return array
     */
    public function isGrantedProvider()
    {
        return [
            'ACL resource given'          => [
                self::TEST_ACL_NAME,
                false
            ],
            'ACL combined resource given' => [
                sprintf('%s;%s', self::TEST_ACL_NAME, self::TEST_ACL_DESCRIPTOR),
                true
            ]
        ];
    }

    /**
     * @param array $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Builder
     */
    protected function getBuilderMock($methods = ['build'])
    {
        $args = [
            self::DEFAULT_DATAGRID_CLASS,
            self::DEFAULT_ACCEPTOR_CLASS,
            $this->eventDispatcher,
            $this->securityFacade
        ];
        return $this->getMockBuilder(Builder::class)
            ->setConstructorArgs($args)
            ->setMethods($methods)->getMock();
    }
}
