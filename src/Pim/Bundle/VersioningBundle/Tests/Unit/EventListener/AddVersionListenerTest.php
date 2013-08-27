<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\VersioningBundle\EventListener\AddVersionListener;
use Pim\Bundle\ProductBundle\Entity\FamilyTranslation;
use Pim\Bundle\ProductBundle\Entity\Family;
use Pim\Bundle\ProductBundle\Entity\AttributeOptionValue;
use Pim\Bundle\ProductBundle\Entity\AttributeOption;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Pim\Bundle\ProductBundle\Entity\ProductPrice;
use Pim\Bundle\ProductBundle\Entity\ProductValue;
use Pim\Bundle\ProductBundle\Entity\Product;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddVersionListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test related method
     */
    public function testGetSubscribedEvents()
    {
        $listener = new AddVersionListener();
        $this->assertEquals($listener->getSubscribedEvents(), array('onFlush', 'postFlush'));
    }

    /**
     * Test related method
     */
    public function testSetUsername()
    {
        $listener = new AddVersionListener();
        $listener->setUsername('admin');
        $user = new User();
        $listener->setUsername($user);
    }

    /**
     * Test related method
     * @expectedException \InvalidArgumentException
     */
    public function testSetUsernameException()
    {
        $listener = new AddVersionListener();
        $listener->setUsername(null);
    }

    /**
     * Test related method
     */
    public function testCheckScheduledUpdate()
    {
        $listener = new AddVersionListener();

        $emMock          = $this->getEntityManagerMock();
        $versionableMock = $this->getVersionableMock('{"field1":  "value1"}');
        $listener->checkScheduledUpdate($emMock, $versionableMock);

        $value = new ProductValue();
        $value->setEntity(new Product());
        $listener->checkScheduledUpdate($emMock, $value);

        $price = new ProductPrice();
        $value->addPrice($price);
        $listener->checkScheduledUpdate($emMock, $price);

        $attribute = new ProductAttribute();
        $listener->checkScheduledUpdate($emMock, $attribute);

        $option = new AttributeOption();
        $attribute->addOption($option);
        $listener->checkScheduledUpdate($emMock, $option);

        $optionValue = new AttributeOptionValue();
        $option->addOptionValue($optionValue);
        $listener->checkScheduledUpdate($emMock, $optionValue);

        $family = new Family();
        $translation = new FamilyTranslation();
        $translation->setForeignKey($family);
        $listener->checkScheduledUpdate($emMock, $translation);
    }

    /**
     * @param string $data
     *
     * @return VersionableInterface
     */
    protected function getVersionableMock($data)
    {
        $versionable = $this->getMock('Pim\Bundle\VersioningBundle\Entity\VersionableInterface');

        $versionable->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        $versionable->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue(2));

        return $versionable;
    }

    /**
     * @return Doctrine\ORM\EntityRepository
     */
    protected function getEntityManagerMock()
    {
        $uowMock = $this
            ->getMockBuilder('Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();
        $uowMock->expects($this->any())
            ->method('computeChangeSet')
            ->will($this->returnValue(true));

        $metaMock = $this
            ->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $emMock = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $repos = array(
            array('OroUserBundle:User', $this->getUserRepositoryMock()),
            array('PimVersioningBundle:Version', $this->getVersionRepositoryMock()),
        );
        $emMock->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValueMap($repos));
        $emMock->expects($this->any())
            ->method('getUnitOfWork')
            ->will($this->returnValue($uowMock));
        $emMock->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue($metaMock));

        return $emMock;
    }

    /**
     * @return Doctrine\ORM\EntityRepository
     */
    protected function getUserRepositoryMock()
    {
        $repo = $this
            ->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($this->getMock('Oro\Bundle\UserBundle\Entity\User')));

        return $repo;
    }

    /**
     * @return Doctrine\ORM\EntityRepository
     */
    protected function getVersionRepositoryMock()
    {
        $repo = $this
            ->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue(null));

        return $repo;
    }

    /**
     * @return OnFlushEventArgs
     */
    protected function getOnFlushEventArgsMock()
    {
        $mock = $this
            ->getMockBuilder('Doctrine\ORM\Event\OnFlushEventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->any())
            ->method('getEntityManager')
            ->will($this->returnValue($this->getEntityManagerMock()));

        return $mock;
    }
}
