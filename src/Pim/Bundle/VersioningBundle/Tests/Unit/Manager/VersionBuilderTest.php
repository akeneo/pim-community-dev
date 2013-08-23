<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\Manager;

use Pim\Bundle\VersioningBundle\Manager\VersionBuilder;
use Pim\Bundle\VersioningBundle\Entity\Version;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Pim\Bundle\VersioningBundle\Manager\VersionBuilder
     */
    protected $builder;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->builder = new VersionBuilder();
    }

    /**
     * Test related method
     */
    public function testBuildVersion()
    {
        $data = array('field' => 'value');
        $version = $this->builder->buildVersion($this->getVersionableMock($data), $this->getUserMock());
        $this->assertTrue($version instanceof Version);
    }

    /**
     * Test related method
     */
    public function testBuildAudit()
    {
        // update version
        $data = array('field1' => 'the-same', 'field2' => 'will-be-changed');
        $previousVersion = $this->builder->buildVersion($this->getVersionableMock($data), $this->getUserMock());

        $data = array('field1' => 'the-same', 'field2' => 'has-changed', 'field3' => 'new-data');
        $currentVersion = $this->builder->buildVersion($this->getVersionableMock($data), $this->getUserMock());

        $audit = $this->builder->buildAudit($currentVersion, $previousVersion);
        $expected = array(
            'field2' => array('old' => 'will-be-changed', 'new' => 'has-changed'),
            'field3' => array('old' => '', 'new' => 'new-data'),
        );
        $this->assertEquals($audit->getData(), $expected);

        // new version
        $audit = $this->builder->buildAudit($currentVersion);
        $expected = array(
            'field1' => array('old' => '', 'new' => 'the-same'),
            'field2' => array('old' => '', 'new' => 'has-changed'),
            'field3' => array('old' => '', 'new' => 'new-data'),
        );
        $this->assertEquals($audit->getData(), $expected);
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
     * @return User
     */
    protected function getUserMock()
    {
        return $this->getMock('Oro\Bundle\UserBundle\Entity\User');
    }
}
