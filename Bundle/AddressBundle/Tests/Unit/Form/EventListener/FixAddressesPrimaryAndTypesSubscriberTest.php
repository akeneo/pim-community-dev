<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\EventListener;

use Oro\Bundle\AddressBundle\Form\EventListener\FixAddressesPrimaryAndTypesSubscriber;
use Symfony\Component\Form\FormEvents;

class FixAddressesPrimaryAndTypesSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FixAddressesPrimaryAndTypesSubscriber
     */
    protected $subscriber;

    protected function setUp()
    {
        $this->subscriber = new FixAddressesPrimaryAndTypesSubscriber('owner.address');
    }


    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            array(FormEvents::POST_SUBMIT => 'postSubmit'),
            $this->subscriber->getSubscribedEvents()
        );
    }

    public function testPostSubmit()
    {
        $this->markTestIncomplete('Implement tests for primary and types');
    }
}
