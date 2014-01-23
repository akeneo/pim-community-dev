<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\EventListener;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\ImportExportBundle\Encoder\CsvEncoder;
use Pim\Bundle\VersioningBundle\EventListener\AddVersionListener;
use Pim\Bundle\VersioningBundle\Builder\VersionBuilder;
use Pim\Bundle\VersioningBundle\Builder\AuditBuilder;
use Pim\Bundle\VersioningBundle\UpdateGuesser\ChainedUpdateGuesser;
use Pim\Bundle\CatalogBundle\Model\Product;

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
        $listener = $this->getListener();
        $this->assertEquals($listener->getSubscribedEvents(), ['onFlush', 'postFlush']);
    }

    /**
     * Test related method
     */
    public function testSetUsername()
    {
        $listener = $this->getListener();
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
        $listener = $this->getListener();
        $listener->setUsername(null);
    }

    /**
      * @return AddVersionListener
      */
    protected function getListener()
    {
        $encoders    = [new CsvEncoder()];
        $normalizers = [new GetSetMethodNormalizer()];
        $serializer  = new Serializer($normalizers, $encoders);
        $versionBuilder = new VersionBuilder($serializer);
        $auditBuilder   = new AuditBuilder();
        $listener = new AddVersionListener($versionBuilder, $auditBuilder, new ChainedUpdateGuesser());

        return $listener;
    }

    /**
     * @param string $data
     *
     * @return Product
     */
    protected function getVersionableMock($data)
    {
        $versionable = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');

        $versionable->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        return $versionable;
    }

    /**
     * @return Doctrine\ORM\EntityManager
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
        $repos = [
            ['OroUserBundle:User', $this->getUserRepositoryMock()],
            ['PimVersioningBundle:Version', $this->getVersionRepositoryMock()],
        ];
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
