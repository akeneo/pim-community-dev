<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\MassEditAction;

use Pim\Bundle\CatalogBundle\MassEditAction\ChangeStatus;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChangeStatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->objectManager = $this->getObjectManagerMock();
        $manager             = $this->getFlexibleManagerMock($this->objectManager);
        $this->action        = new ChangeStatus($manager);
    }

    /**
     * Test related method
     */
    public function testInstanceOfMassEditAction()
    {
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\MassEditAction\MassEditActionInterface', $this->action);
    }

    /**
     * Test related method
     */
    public function testEnableManyProducts()
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

        $this->action->setToEnable(false);

        $this->action->perform(array($foo, $bar));
    }

    /**
     * Test related method
     */
    public function testDisableManyProducts()
    {
        $foo = $this->getProductMock();
        $foo->expects($this->once())
            ->method('setEnabled')
            ->with(true);

        $bar = $this->getProductMock();
        $bar->expects($this->once())
            ->method('setEnabled')
            ->with(true);

        $this->objectManager
            ->expects($this->once())
            ->method('flush');

        $this->action->setToEnable(true);

        $this->action->perform(array($foo, $bar));
    }

    /**
     * @param mixed $objectManager
     *
     * @return \Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager
     */
    protected function getFlexibleManagerMock($objectManager)
    {
        $manager = $this
            ->getMockBuilder('Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager')
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->any())
            ->method('getStorageManager')
            ->will($this->returnValue($objectManager));

        return $manager;
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getObjectManagerMock()
    {
        return $this->getMock('Doctrine\Common\Persistence\ObjectManager');
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Entity\Product
     */
    protected function getProductMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Entity\Product');
    }
}
