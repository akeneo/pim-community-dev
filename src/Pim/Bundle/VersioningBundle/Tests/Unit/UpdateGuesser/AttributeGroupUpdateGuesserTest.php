<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\UpdateGuesser;

use Pim\Bundle\VersioningBundle\UpdateGuesser\AttributeGroupUpdateGuesser;
use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupUpdateGuesserTest extends AbstractUpdateGuesserTest
{
    /**
     * Test related methods
     */
    public function testGuessUpdates()
    {
        $attribute = new ProductAttribute();
        $group     = new AttributeGroup();
        $attribute->setGroup($group);
        $guesser   = new AttributeGroupUpdateGuesser();
        $em        = $this->getEntityManagerMock($group);
        $updates   = $guesser->guessUpdates($em, $attribute, UpdateGuesserInterface::ACTION_UPDATE_ENTITY);
        $this->assertEquals(2, count($updates));
        $this->assertEquals($attribute, $updates[0]);
        $this->assertEquals($group, $updates[1]);
    }

    /**
     * @param AttributeGroup $group
     *
     * @return EntityManager
     */
    protected function getEntityManagerMock($group)
    {
        $mock = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $mock
            ->expects($this->any())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->getUnitOfWorkMock($group)));

        return $mock;
    }

    /**
     * @param AttributeGroup $group
     *
     * @return Doctrine\ORM\UnitOfWork
     */
    protected function getUnitOfWorkMock($group)
    {
        $mock = $this
            ->getMockBuilder('Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();
        $mock
            ->expects($this->any())
            ->method('getEntityChangeSet')
            ->will($this->returnValue(array('group' => array($group))));

        return $mock;
    }
}
