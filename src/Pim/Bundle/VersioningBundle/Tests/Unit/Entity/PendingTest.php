<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\Entity;

use Pim\Bundle\VersioningBundle\Entity\Pending;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PendingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test related methods
     */
    public function testGetterSetter()
    {
        $pending = new Pending('name', 1, 'user');
        $this->assertEquals($pending->getResourceName(), 'name');
        $this->assertEquals($pending->getResourceId(), 1);
        $this->assertEquals($pending->getUsername(), 'user');
    }
}
