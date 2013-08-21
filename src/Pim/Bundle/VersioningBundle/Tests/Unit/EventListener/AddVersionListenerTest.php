<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\EventListener;

use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\VersioningBundle\EventListener\AddVersionListener;
use Pim\Bundle\VersioningBundle\Manager\VersionBuilder;

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
        $builder  = new VersionBuilder();
        $listener = new AddVersionListener($builder);
        $this->assertEquals($listener->getSubscribedEvents(), array('onFlush', 'postFlush'));
    }

    /**
     * Test related method
     */
    public function testSetUsername()
    {
        $builder  = new VersionBuilder();
        $listener = new AddVersionListener($builder);
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
        $builder  = new VersionBuilder();
        $listener = new AddVersionListener($builder);
        $listener->setUsername(null);
    }

    /**
     * Test related method
     */
    public function testcheckScheduledUpdate()
    {
        $builder  = new VersionBuilder();
        $listener = new AddVersionListener($builder);

        $emMock          = $this->getEntityManagerMock();
        $versionableMock = $this->getVersionableMock(array('field1' => 'value1'));

        $listener->checkScheduledUpdate($emMock, $versionableMock);
    }

    /**
     * Test related method
     */
    public function testWriteSnapshot()
    {
        $builder  = new VersionBuilder();
        $listener = new AddVersionListener($builder);

        $emMock          = $this->getEntityManagerMock();
        $versionableMock = $this->getVersionableMock(array('field1' => 'value1'));
        $userMock        = $this->getMock('Oro\Bundle\UserBundle\Entity\User');

        $listener->writeSnapshot($emMock, $versionableMock, $userMock);
    }

    /**
     * @param array $data
     *
     * @return VersionableInterface
     */
    protected function getVersionableMock(array $data)
    {
        $versionable = $this->getMock('Pim\Bundle\VersioningBundle\Entity\VersionableInterface');

        $versionable->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        $versionable->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue(2));

        $versionable->expects($this->any())
            ->method('getVersionedData')
            ->will($this->returnValue($data));

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
}
