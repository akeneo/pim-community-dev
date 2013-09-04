<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\BatchOperation;

use Pim\Bundle\CatalogBundle\BatchOperation\ChangeStatus;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChangeStatusTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->objectManager = $this->getObjectManagerMock();
        $manager             = $this->getFlexibleManagerMock($this->objectManager);
        $this->operation     = new ChangeStatus($manager);
    }

    public function testInstanceOfBatchOperation()
    {
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\BatchOperation\BatchOperation', $this->operation);
    }

    public function testPerform()
    {
        $foo = $this->getProductMock();
        $foo->expects($this->once())
            ->method('setEnabled')
            ->with(false);

        $bar = $this->getProductMock();
        $bar->expects($this->once())
            ->method('setEnabled')
            ->with(false);

        $this->objectManager
            ->expects($this->once())
            ->method('flush');

        $this->operation->setEnable(false);

        $this->operation->perform(array($foo, $bar));
    }

    protected function getFlexibleManagerMock($objectManager)
    {
        $manager = $this
            ->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager')
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->any())
            ->method('getStorageManager')
            ->will($this->returnValue($objectManager));

        return $manager;
    }

    protected function getObjectManagerMock()
    {
        return $this->getMock('Doctrine\Common\Persistence\ObjectManager');
    }

    protected function getProductMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Entity\Product');
    }
}
