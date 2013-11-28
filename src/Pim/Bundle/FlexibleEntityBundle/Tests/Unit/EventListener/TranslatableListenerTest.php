<?php
namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\EventListener;

use Pim\Bundle\FlexibleEntityBundle\Tests\Unit\AbstractFlexibleManagerTest;
use Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Demo\Flexible;
use Pim\Bundle\FlexibleEntityBundle\EventListener\TranslatableListener;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslatableListenerTest extends AbstractFlexibleManagerTest
{
    /**
     * @var Flexible
     */
    protected $flexible;

    /**
     * Set up unit test
     */
    public function setUp()
    {
        parent::setUp();
        // create listener
        $this->listener = new TranslatableListener();
        $this->listener->setContainer($this->container);
        // create flexible entity
        $this->flexible = new Flexible();
    }

    /**
     * test related method
     */
    public function testGetSubscribedEvents()
    {
        $events = array('postLoad');
        $this->assertEquals($this->listener->getSubscribedEvents(), $events);
    }

    /**
     * test related method
     */
    public function testPostLoad()
    {
        // check before
        $this->assertNull($this->flexible->getLocale());
        // call method
        $args = new LifecycleEventArgs($this->flexible, $this->entityManager);
        $this->listener->postLoad($args);
        // check after (locale is setup)
        $this->assertEquals($this->flexible->getLocale(), $this->defaultLocale);
        // change locale from manager, and re-call
        $code = 'it_IT';
        $this->manager->setLocale($code);
        $this->listener->postLoad($args);
        //locale heas been changed by post load
        $this->assertEquals($this->flexible->getLocale(), $code);
    }
}
