<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\Manager;

use Oro\Bundle\DataAuditBundle\Entity\Audit;
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
        $versionable = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');
        $entries = $this->manager->getLogEntries($versionable);
        $this->assertEquals($entries, $this->entries);
    }

    /**
     * Test related method
     */
    public function testGetNewestLogEntry()
    {
        $versionable = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');
        $entry = $this->manager->getNewestLogEntry($versionable);
        $this->assertEquals($entry, reset($this->entries));
    }

    /**
     * Test related method
     */
    public function testGetOldestLogEntry()
    {
        $versionable = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');
        $entry = $this->manager->getOldestLogEntry($versionable);
        $this->assertEquals($entry, end($this->entries));
    }

    /**
     * @return User
     */
    protected function getUserMock()
    {
        return $this->getMock('Oro\Bundle\UserBundle\Entity\User');
    }

    /**
     * @return EntityRepository
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
