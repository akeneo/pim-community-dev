<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Form\Subscriber;

use Pim\Bundle\ProductBundle\Form\Subscriber\TransformImportedProductDataSubscriber;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TransformImportedProductDataSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceOfEventSubscriber()
    {
        $this->assertInstanceOf(
            'Symfony\Component\EventDispatcher\EventSubscriberInterface',
            new TransformImportedProductDataSubscriber
        );
    }

    public function testSubscribedToNothing()
    {
        $this->assertEmpty(TransformImportedProductDataSubscriber::getSubscribedEvents());
    }
}
