<?php

namespace Oro\Bundle\OrganizationBundlee\Tests\Unit\Twig;

use Oro\Bundle\OrganizationBundle\Twig\OwnerTypeExtension;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;

class OwnerTypeExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OwnerTypeExtension
     */
    private $extension;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $configProvider;

    /**
     * Set up test environment
     */
    public function setUp()
    {
        $this->configProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()->getMock();
        $this->extension = new OwnerTypeExtension($this->configProvider);
    }

    public function testName()
    {
        $this->assertEquals('oro_owner_type', $this->extension->getName());
    }

    public function testGetOwnerType()
    {
        $entity = new BusinessUnit();
        $className = 'Oro\Bundle\OrganizationBundle\Entity\BusinessUnit';
        $config = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigInterface')
            ->disableOriginalConstructor()->getMock();
        $config->expects($this->once())->method('all')->will($this->returnValue(array('owner_type' => 'test_type')));

        $this->configProvider->expects($this->once())->method('hasConfig')->with($this->equalTo($className))
            ->will($this->returnValue(true));
        $this->configProvider->expects($this->once())->method('getConfig')->with($this->equalTo($className))
            ->will($this->returnValue($config));
        $this->assertEquals('test_type', $this->extension->getOwnerType($entity));
    }

    public function testGetFunctions()
    {
        $this->assertArrayHasKey('oro_get_owner_type', $this->extension->getFunctions());
    }
}
