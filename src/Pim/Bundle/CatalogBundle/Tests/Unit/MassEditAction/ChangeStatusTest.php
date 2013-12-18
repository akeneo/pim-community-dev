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
        $this->action = new ChangeStatus();
    }

    /**
     * Test related method
     */
    public function testInstanceOfMassEditAction()
    {
        $this->assertInstanceOf(
            'Pim\Bundle\CatalogBundle\MassEditAction\MassEditActionInterface',
            $this->action
        );
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

        $this->action->setToEnable(true);

        $this->action->perform(array($foo, $bar));
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Model\Product
     */
    protected function getProductMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');
    }
}
