<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\MassEditAction;

use Pim\Bundle\CatalogBundle\MassEditAction\AddToGroups;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddToGroupsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->xsell          = $this->getGroupMock();
        $this->upsell         = $this->getGroupMock();
        $this->groups         = array($this->xsell, $this->upsell);
        $this->entityManager  = $this->getEntityManagerMock($this->groups);
        $this->action         = new AddToGroups($this->entityManager);
    }

    public function testIsAMassEditAction()
    {
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\MassEditAction\MassEditActionInterface', $this->action);
    }

    /**
     * @ignore
     * Test related method
     */
    public function testPerform()
    {
        $products = array(
            $this->getProductMock(),
            $this->getProductMock(),
            $this->getProductMock(),
        );

        $this->xsell->expects($this->at(0))->method('addProduct')->with($products[0]);
        $this->xsell->expects($this->at(1))->method('addProduct')->with($products[1]);
        $this->xsell->expects($this->at(2))->method('addProduct')->with($products[2]);

        $this->upsell->expects($this->at(0))->method('addProduct')->with($products[0]);
        $this->upsell->expects($this->at(1))->method('addProduct')->with($products[1]);
        $this->upsell->expects($this->at(2))->method('addProduct')->with($products[2]);

        $this->action->setGroups($this->groups);
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
     * @return Pim\Bundle\CatalogBundle\Model\Group
     */
    protected function getGroupMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Entity\Group');
    }

    /**
     * @param array $groups
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManagerMock($groups)
    {
        $em = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->any())
            ->method('getRepository')
            ->will(
                $this->returnValue(
                    $this->getGroupRepositoryMock(
                        $groups
                    )
                )
            );

        return $em;
    }

    /**
     * @param array $groups
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getGroupRepositoryMock(array $groups)
    {
        $repository = $this
            ->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repository->expects($this->any())
            ->method('findAll')
            ->will($this->returnValue($groups));

        return $repository;
    }
}
