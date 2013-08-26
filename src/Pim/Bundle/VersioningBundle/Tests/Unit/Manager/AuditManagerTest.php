<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\Manager;

use Oro\Bundle\DataAuditBundle\Entity\Audit;
use Pim\Bundle\VersioningBundle\Entity\Version;
use Pim\Bundle\VersioningBundle\Manager\AuditManager;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuditManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Pim\Bundle\VersioningBundle\Manager\AuditManager
     */
    protected $manager;

    /**
     * @var Audit[]
     */
    protected $entries = array();

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $first = new Audit();
        $second = new Audit();
        $third = new Audit();
        $this->entries = array($first, $second, $third);
        $this->manager = new AuditManager($this->getEntityManagerMock());
    }

    /**
     * Test related method
     */
    public function testGetLogEntries()
    {
        $versionable = $this->getMock('Pim\Bundle\VersioningBundle\Entity\VersionableInterface');
        $entries = $this->manager->getLogEntries($versionable);
        $this->assertEquals($entries, $this->entries);
    }

    /**
     * Test related method
     */
    public function testGetFirstLogEntry()
    {
        $versionable = $this->getMock('Pim\Bundle\VersioningBundle\Entity\VersionableInterface');
        $entry = $this->manager->getFirstLogEntry($versionable);
        $this->assertEquals($entry, current($this->entries));
    }

    /**
     * Test related method
     */
    public function testGetLastLogEntry()
    {
        $versionable = $this->getMock('Pim\Bundle\VersioningBundle\Entity\VersionableInterface');
        $entry = $this->manager->getLastLogEntry($versionable);
        $this->assertEquals($entry, end($this->entries));
    }

    /**
     * Test related method
     */
    public function testBuildAudit()
    {
        $resourceName = 'myfakeresourcename';
        $resourceId = 1;
        $user = $this->getUserMock();
        $numVersion = 1;

        // update version
        $data = array('field1' => 'the-same', 'field2' => 'will-be-changed');
        $previousVersion = new Version($resourceName, $resourceId, $numVersion, $data, $user);

        $data = array('field1' => 'the-same', 'field2' => 'has-changed', 'field3' => 'new-data');
        $currentVersion = new Version($resourceName, $resourceId, $numVersion, $data, $user);

        $audit = $this->manager->buildAudit($currentVersion, $previousVersion);
        $expected = array(
            'field2' => array('old' => 'will-be-changed', 'new' => 'has-changed'),
            'field3' => array('old' => '', 'new' => 'new-data'),
        );
        $this->assertEquals($expected, $audit->getData());

        // new version
        $audit = $this->manager->buildAudit($currentVersion);
        $expected = array(
            'field1' => array('old' => '', 'new' => 'the-same'),
            'field2' => array('old' => '', 'new' => 'has-changed'),
            'field3' => array('old' => '', 'new' => 'new-data'),
        );
        $this->assertEquals($audit->getData(), $expected);

    }

    /**
     * @return User
     */
    protected function getUserMock()
    {
        return $this->getMock('Oro\Bundle\UserBundle\Entity\User');
    }

    /**
     * @return AuditRepository
     */
    protected function getAuditRepositoryMock()
    {
        $repo = $this
            ->getMockBuilder('Oro\Bundle\DataAuditBundle\Entity\Repository\AuditRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repo->expects($this->any())
            ->method('getLogEntries')
            ->will($this->returnValue($this->entries));

        return $repo;
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
            ->will($this->returnValue($this->getAuditRepositoryMock()));

        return $mock;
    }
}
