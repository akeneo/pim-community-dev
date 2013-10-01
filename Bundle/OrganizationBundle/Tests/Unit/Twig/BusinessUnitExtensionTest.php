<?php

namespace Oro\Bundle\OrganizationBundlee\Tests\Unit\Twig;

use Oro\Bundle\OrganizationBundle\Twig\BusinessUnitExtension;

class BusinessUnitExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BusinessUnitExtension
     */
    private $extension;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $manager;

    /**
     * Set up test environment
     */
    public function setUp()
    {
        $this->manager = $this->getMockBuilder('Oro\Bundle\OrganizationBundle\Entity\Manager\BusinessUnitManager')
            ->disableOriginalConstructor()->getMock();
        $this->extension = new BusinessUnitExtension($this->manager);
    }

    public function testName()
    {
        $this->assertEquals('oro_business_unit', $this->extension->getName());
    }

    public function testGetBusinessUnitCount()
    {
        $repo = $this->getMockBuilder('Oro\Bundle\OrganizationBundle\Entity\Repository\BusinessUnitRepository')
            ->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())->method('getBusinessUnitsCount')->will($this->returnValue(2));
        $this->manager->expects($this->once())->method('getBusinessUnitRepo')->will($this->returnValue($repo));
        $this->assertEquals(2, $this->extension->getBusinessUnitCount());
    }

    public function testGetFunctions()
    {
        $this->assertArrayHasKey('oro_get_business_units_count', $this->extension->getFunctions());
    }
}
