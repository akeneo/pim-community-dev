<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\Entity;

use Pim\Bundle\VersioningBundle\Entity\Version;
use Pim\Bundle\VersioningBundle\Entity\VersionableInterface;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Pim\Bundle\VersioningBundle\Entity\Version
     */
    protected $version;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $user          = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
        $versionable   = $this->getVersionableMock();
        $resourceName  = get_class($versionable);
        $resourceId    = $versionable->getId();
        $numVersion    = $versionable->getVersion();
        $data          = $versionable->getVersionedData();
        $this->version = new Version($resourceName, $resourceId, $numVersion, $data, $user);
    }

    /**
     * Test related methods
     */
    public function testGetterSetter()
    {
        $this->assertEquals($this->version->getResourceId(), 1);
        $this->assertEquals($this->version->getVersion(), 2);
        $this->assertEquals($this->version->getVersionedData(), array('field' => 'value'));
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
}
