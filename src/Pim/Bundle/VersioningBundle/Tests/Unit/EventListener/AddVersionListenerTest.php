<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\EventListener;

use Oro\Bundle\UserBundle\Entity\User;

use Pim\Bundle\VersioningBundle\EventListener\AddVersionListener;
use Pim\Bundle\VersioningBundle\Manager\VersionBuilder;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddVersionListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Pim\Bundle\VersioningBundle\EventListener\AddVersionListener
     */
    protected $listener;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $builder = new VersionBuilder();
        $this->listener = new AddVersionListener($builder);
    }

    /**
     * Test related method
     */
    public function testGetSubscribedEvents()
    {
        $this->assertEquals($this->listener->getSubscribedEvents(), array('onFlush'));
    }

    /**
     * Test related method
     */
    public function testSetUsername()
    {
        $this->listener->setUsername('admin');
        $user = new User();
        $this->listener->setUsername($user);
    }

    /**
     * Test related method
     * @expectedException \InvalidArgumentException
     */
    public function testSetUsernameException()
    {
        $this->listener->setUsername(null);
    }
}
