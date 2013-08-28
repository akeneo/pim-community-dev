<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Pim\Bundle\ProductBundle\EventListener\UpdateCompletenessListener;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateCompletenessListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test related method
     */
    public function testGetSubscribedEvents()
    {
        $listener = new UpdateCompletenessListener();
        $this->assertEquals($listener->getSubscribedEvents(), array('postPersist', 'postUpdate', 'onFlush', 'postFlush'));
    }
}
