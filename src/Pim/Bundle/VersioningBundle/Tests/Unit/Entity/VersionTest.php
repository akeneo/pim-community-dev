<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\Entity;

use Pim\Bundle\VersioningBundle\Entity\Version;
use Pim\Bundle\CatalogBundle\Model\Product;

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
        $numVersion    = 2;
        $this->version = new Version($resourceName, $resourceId, $numVersion, '{"field":  "value"}', $user);
    }

    /**
     * Test related methods
     */
    public function testGetterSetter()
    {
        $this->assertEquals($this->version->getResourceId(), 1);
        $this->assertEquals($this->version->getVersion(), 2);
        $this->assertEquals($this->version->getData(), '{"field":  "value"}');
    }

    /**
     * @return Product
     */
    protected function getVersionableMock()
    {
        $versionable = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');

        $versionable->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        return $versionable;
    }
}
