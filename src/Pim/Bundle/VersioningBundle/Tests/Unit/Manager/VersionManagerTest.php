<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\Manager;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Serializer\Serializer;
use Pim\Bundle\ImportExportBundle\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Bundle\VersioningBundle\Entity\Version;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Pim\Bundle\VersioningBundle\Manager\VersionManager
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
        $this->manager = new VersionManager($this->getEntityManagerMock(), $serializer);
    }

    /**
     * Test related method
     */
    public function testBuildVersion()
    {
        $data = array('field' => 'value');
        $version = $this->manager->buildVersion($this->getVersionableMock($data), $this->getUserMock());
        $this->assertTrue($version instanceof Version);
    }

    /**
     * Test related method
     */
    public function testGetPreviousVersion()
    {
        $version = $this->manager->buildVersion($this->getVersionableMock(array()), $this->getUserMock());
        $previous = $this->manager->getPreviousVersion($version);
        $this->assertTrue($previous instanceof Version);
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
            ->will($this->returnValue(new Version('a', 1, 1, array(), $this->getUserMock())));

        return $repo;
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

        return $versionable;
    }

    /**
     * @return User
     */
    protected function getUserMock()
    {
        return $this->getMock('Oro\Bundle\UserBundle\Entity\User');
    }
}
