<?php

namespace Pim\Bundle\EnrichBundle\Tests\Unit\MassEditAction;

use Pim\Bundle\EnrichBundle\MassEditAction\ChangeFamily;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChangeFamilyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->action = new ChangeFamily();
    }

    /**
     * Test related method
     */
    public function testPerform()
    {
        $products = array(
            $this->getProductMock(),
            $this->getProductMock(),
            $this->getProductMock(),
        );
        $family = $this->getFamilyMock();

        $products[0]->expects($this->once())
            ->method('setFamily')
            ->with($family);

        $products[1]->expects($this->once())
            ->method('setFamily')
            ->with($family);

        $products[2]->expects($this->once())
            ->method('setFamily')
            ->with($family);

        $this->action->setFamily($family);
        $this->action->perform($products);
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Model\Product
     */
    private function getProductMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Entity\Family
     */
    protected function getFamilyMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Entity\Family');
    }
}
