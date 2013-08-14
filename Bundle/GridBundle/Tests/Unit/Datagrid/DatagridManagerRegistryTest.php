<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\ResultRecord;
use Oro\Bundle\GridBundle\Datagrid\DatagridManagerRegistry;

class DatagridManagerRegistryTest extends \PHPUnit_Framework_TestCase
{
    const TEST_NAME         = 'name';
    const TEST_SERVICE_NAME = 'service';

    /** @var DatagridManagerRegistry */
    protected $registry;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $container;

    public function setUp()
    {
        $this->container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->disableOriginalConstructor()->getMock();
        $this->registry = new DatagridManagerRegistry($this->container);
    }

    public function tearDown()
    {
        unset($this->container);
        unset($this->registry);
    }

    /**
     * @dataProvider servicesToAddProvider
     * @param array $servicesToAdd
     * @param bool|string $expectedException
     */
    public function testAddDatagridManagerService($servicesToAdd, $expectedException)
    {
        if ($expectedException) {
            $this->setExpectedException($expectedException);
        }

        foreach ($servicesToAdd as $service) {
            $this->registry->addDatagridManagerService($service['name'], $service['serviceId']);
            $this->assertAttributeContains($service['serviceId'], 'services', $this->registry);
        }
    }

    /**
     * @return array
     */
    public function servicesToAddProvider()
    {
        return array(
            'expected adding service'         => array(
                'services to add' => array(
                    array(
                        'name'      => self::TEST_NAME,
                        'serviceId' => self::TEST_SERVICE_NAME
                    )
                ),
                'exception' => false
            ),
            'expected doble adding exception' => array(
                'services to add' => array(
                    array(
                        'name'      => self::TEST_NAME,
                        'serviceId' => self::TEST_SERVICE_NAME
                    ),
                    array(
                        'name'      => self::TEST_NAME,
                        'serviceId' => self::TEST_SERVICE_NAME
                    )
                ),
                'exception' => '\LogicException'
            )
        );
    }

    public function testHasDatagridManager()
    {
        $this->registry->addDatagridManagerService(self::TEST_NAME, self::TEST_SERVICE_NAME);
        $this->assertTrue($this->registry->hasDatagridManager(self::TEST_NAME));
        $this->assertFalse($this->registry->hasDatagridManager('someNotExistingName'));
    }

    /**
     * @dataProvider datagridManagerProvider
     */
    public function testGetDatagridManager(
        $servicesToAdd,
        $serviceToRetrieve,
        $expectedException,
        $expectedContainerCalls
    ) {
        if ($expectedException) {
            $this->setExpectedException($expectedException);
        }
    }

    /**
     * @return array
     */
    public function datagridManagerProvider()
    {
        return array(
            'trying to access not added manager' => array(),
            'trying to access manager that doesn\'t exist in container' => array(),
            'normal scenario' => array(),
        );
    }
}
