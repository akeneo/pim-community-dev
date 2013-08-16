<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\Entity;

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
     * @var \Pim\Bundle\VersioningBundle\VersionBuilder
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
    public function testBuild()
    {
        $version = $this->builder->buildVersion($this->getVersionableMock(), $this->getUserMock());
        $this->assertTrue($version instanceof Version);
    }

    /**
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

        $versionable->expects($this->any())
            ->method('getVersionedData')
            ->will($this->returnValue(array('field' => 'value')));

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
