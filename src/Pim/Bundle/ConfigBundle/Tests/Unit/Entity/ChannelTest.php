<?php

namespace Pim\Bundle\ConfigBundle\Tests\Unit\Entity;

use Pim\Bundle\ConfigBundle\Entity\Channel;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ChannelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test related method
     */
    public function testConstruct()
    {
        $channel = new Channel();
        $this->assertInstanceOf('Pim\Bundle\ConfigBundle\Entity\Channel', $channel);
    }

    /**
     * Test getter/setter for id property
     */
    public function testGetSetId()
    {
        $channel = new Channel();
        $this->assertEmpty($channel->getId());

        // change value and assert new
        $newId = 5;
        $channel->setId($newId);
        $this->assertEquals($newId, $channel->getId());
    }

    /**
     * Test getter/setter for code property
     */
    public function testGetSetCode()
    {
        $channel = new Channel();
        $this->assertEmpty($channel->getCode());

        // change value and assert new
        $newCode = 'ecommerce';
        $channel->setCode($newCode);
        $this->assertEquals($newCode, $channel->getCode());
    }

    /**
     * Test getter/setter for name property
     */
    public function testGetSetName()
    {
        $channel = new Channel();
        $this->assertEmpty($channel->getName());

        // change value and assert new
        $newName = 'E-Commerce';
        $channel->setName($newName);
        $this->assertEquals($newName, $channel->getName());
    }
}
