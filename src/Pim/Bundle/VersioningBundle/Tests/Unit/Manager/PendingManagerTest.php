<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\Manager;

use Symfony\Component\Serializer\Serializer;
use Pim\Bundle\ImportExportBundle\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Bundle\VersioningBundle\Manager\AuditManager;
use Pim\Bundle\VersioningBundle\Manager\PendingManager;
use Pim\Bundle\VersioningBundle\Entity\Pending;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PendingManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Pim\Bundle\VersioningBundle\Manager\PendingManager
     */
    protected $manager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $encoders = array(new CsvEncoder());
        $normalizers = array(new GetSetMethodNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $versionManager = new VersionManager($this->getEntityManagerMock(), $serializer);
        $auditManager = new AuditManager($this->getEntityManagerMock());

        $this->manager = new PendingManager($versionManager, $auditManager, $this->getEntityManagerMock());
    }

    /**
     * test related method
     */
    public function testGetAllPendingVersions()
    {
        $pendings = $this->manager->getAllPendingVersions();

        $this->assertNotEmpty($pendings);
    }

    /**
     * test related method
     */
    public function testGetPendingVersions()
    {
        $pendings = $this->manager->getPendingVersions($this->getVersionableMock());

        $this->assertNotEmpty($pendings);
    }

    /**
     * test related method
     */
    public function testGetPendingVersion()
    {
        $pending = $this->manager->getPendingVersion($this->getVersionableMock());

        $this->assertTrue($pending instanceof Pending);
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManagerMock()
    {
        $mock = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($this->getRepositoryMock()));

        return $mock;
    }

    /**
     * @return EntityRepository
     */
    protected function getRepositoryMock()
    {
        $repo = $this
            ->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repo->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue(new Pending('resourcename', 1, 'user')));

        $repo->expects($this->any())
            ->method('findBy')
            ->will($this->returnValue(array(new Pending('resourcename', 1, 'user'))));

        $repo->expects($this->any())
            ->method('findAll')
            ->will($this->returnValue(array(new Pending('resourcename', 1, 'user'))));


        return $repo;
    }

    /**
     * @param array $data
     *
     * @return VersionableInterface
     */
    protected function getVersionableMock()
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
}
